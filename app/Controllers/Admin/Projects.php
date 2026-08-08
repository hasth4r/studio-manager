<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\ProjectModel;
use App\Models\ClientModel;
use App\Models\CollaboratorModel;
use App\Models\ProjectTypeModel;

class Projects extends BaseController
{
    protected $projectModel;
    protected $clientModel;
    protected $collaboratorModel;
    protected $projectTypeModel;

    public function __construct()
    {
        $this->projectModel      = new ProjectModel();
        $this->clientModel       = new ClientModel();
        $this->collaboratorModel = new CollaboratorModel();
        $this->projectTypeModel  = new ProjectTypeModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('projects');
        $builder->select('projects.*, clients.company_name as client_name, project_types.name as project_type_name, collaborators.company_name as collaborator_name');
        $builder->join('clients', 'clients.id = projects.client_id', 'left');
        $builder->join('project_types', 'project_types.id = projects.project_type_id', 'left');
        $builder->join('collaborators', 'collaborators.id = projects.collaborator_id', 'left');
        $builder->orderBy('projects.created_at', 'DESC');
        $projects = $builder->get()->getResult();

        $data = [
            'pageTitle' => 'Projects',
            'projects'  => $projects,
        ];

        return view('projects/index', $data);
    }

    public function create()
    {
        $role = session()->get('userRole');
        if (!in_array($role, ['admin', 'project_manager'])) {
            return redirect()->to('/admin/projects')->with('error', 'Unauthorized access.');
        }

        $data = [
            'pageTitle'     => 'New Project',
            'clients'       => $this->clientModel->orderBy('company_name', 'ASC')->findAll(),
            'collaborators' => $this->collaboratorModel->orderBy('company_name', 'ASC')->findAll(),
            'project_types' => $this->projectTypeModel->orderBy('name', 'ASC')->findAll(),
        ];

        return view('projects/create', $data);
    }

    public function store()
    {
        $role = session()->get('userRole');
        if (!in_array($role, ['admin', 'project_manager'])) {
            return redirect()->to('/admin/projects')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'name'            => 'required|min_length[2]|max_length[255]',
            'project_code'    => 'required|max_length[50]|is_unique[projects.project_code]',
            'client_id'       => 'required|is_natural_no_zero',
            'project_type_id' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $collaboratorId = $this->request->getPost('collaborator_id');

        $data = [
            'name'            => $this->request->getPost('name'),
            'project_code'    => $this->request->getPost('project_code'),
            'client_id'       => $this->request->getPost('client_id'),
            'collaborator_id' => $collaboratorId ?: null,
            'project_type_id' => $this->request->getPost('project_type_id'),
            'status'          => $this->request->getPost('status') ?: 'active',
            'start_date'      => $this->request->getPost('start_date') ?: null,
            'deadline'        => $this->request->getPost('deadline') ?: null,
            'priority'        => $this->request->getPost('priority') ?: 'normal',
            'fps'             => (int)($this->request->getPost('fps') ?: 24),
        ];

        if ($this->projectModel->insert($data)) {
            // Auto-create local folder structure!
            \App\Libraries\FolderManager::createProjectFolders($data['project_code']);

            return redirect()->to('/admin/projects')->with('message', 'Project created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create project.');
    }

    public function syncFolders($id)
    {
        if (session()->get('userRole') !== 'admin' && session()->get('userRole') !== 'project_manager') {
            return redirect()->to('/admin/projects')->with('error', 'Unauthorized.');
        }

        if (\App\Libraries\FolderManager::syncProjectFolders($id)) {
            return redirect()->back()->with('message', 'Project folder structure synchronized successfully.');
        }

        return redirect()->back()->with('error', 'Failed to synchronize folders. Please check server paths and permissions.');
    }

    public function show($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        // Fetch Project Details
        $builder = $db->table('projects');
        $builder->select('projects.*, clients.company_name as client_name, project_types.name as project_type_name, collaborators.company_name as collaborator_name');
        $builder->join('clients', 'clients.id = projects.client_id', 'left');
        $builder->join('project_types', 'project_types.id = projects.project_type_id', 'left');
        $builder->join('collaborators', 'collaborators.id = projects.collaborator_id', 'left');
        $builder->where('projects.id', $id);
        $project = $builder->get()->getRow();

        if (!$project) {
            return redirect()->to('/admin/projects')->with('error', 'Project not found.');
        }

        // Models
        $sequenceModel = new \App\Models\SequenceModel();
        $shotModel = new \App\Models\ShotModel();
        $assetModel = new \App\Models\AssetModel();

        // Fetch Sequences & Shots & Assets
        $sequences = $sequenceModel->where('project_id', $id)->orderBy('created_at', 'ASC')->findAll();
        $shots = $shotModel->where('project_id', $id)->orderBy('shot_number', 'ASC')->findAll();
        $assets = $assetModel->where('project_id', $id)->orderBy('name', 'ASC')->findAll();

        // Analytics Data
        $totalSequences = count($sequences);
        $totalShots = count($shots);
        $totalAssets = count($assets);
        
        $taskModel = new \App\Models\TaskModel();
        $tasks = $taskModel->where('project_id', $id)->findAll();
        $totalTasks = count($tasks);
        $completedTasks = count(array_filter($tasks, fn($t) => $t->status === 'completed' || $t->status === 'approved'));
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Benchmarks Data
        $taskTypeModel = new \App\Models\TaskTypeModel();
        $benchmarkModel = new \App\Models\TaskBenchmarkModel();
        $taskTypes = $taskTypeModel->orderBy('name', 'ASC')->findAll();
        $benchmarks = $benchmarkModel->getProjectBenchmarks($id);

        $data = [
            'pageTitle'      => 'Project: ' . $project->name,
            'project'        => $project,
            'sequences'      => $sequences,
            'shots'          => $shots,
            'assets'         => $assets,
            'taskTypes'      => $taskTypes,
            'benchmarks'     => $benchmarks,
            'analytics'      => [
                'sequences' => $totalSequences,
                'shots'     => $totalShots,
                'assets'    => $totalAssets,
                'progress'  => $progress
            ]
        ];

        return view('projects/show', $data);
    }

    public function storeSequence()
    {
        $projectId = $this->request->getPost('project_id');
        $rules = [
            'project_id' => 'required|is_natural_no_zero',
            'name'       => 'required|max_length[150]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/projects/' . $projectId)->with('error', 'Validation failed for Sequence.');
        }

        $model = new \App\Models\SequenceModel();
        $model->insert([
            'project_id'  => $projectId,
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);
        \App\Libraries\FolderManager::createSequenceFolders($project->project_code, $this->request->getPost('name'));

        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Sequence added successfully.');
    }

    public function storeShot()
    {
        $projectId = $this->request->getPost('project_id');
        $rules = [
            'project_id'  => 'required|is_natural_no_zero',
            'shot_number' => 'required|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/projects/' . $projectId)->with('error', 'Validation failed for Shot.');
        }

        $model = new \App\Models\ShotModel();
        
        $thumbnailPath = null;
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/shots', $newName);
            $thumbnailPath = 'uploads/shots/' . $newName;
        }

        $sequenceId = $this->request->getPost('sequence_id');

        $model->insert([
            'project_id'     => $projectId,
            'sequence_id'    => empty($sequenceId) ? null : $sequenceId,
            'shot_number'    => $this->request->getPost('shot_number'),
            'description'    => $this->request->getPost('description'),
            'thumbnail_path' => $thumbnailPath,
            'fps'            => $this->request->getPost('fps') ? (int)$this->request->getPost('fps') : null,
            'frame_count'    => $this->request->getPost('frame_count') ? (int)$this->request->getPost('frame_count') : null,
        ]);

        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);
        
        $sequenceModel = new \App\Models\SequenceModel();
        $sequence = $sequenceModel->find($sequenceId);
        
        if ($sequence) {
            \App\Libraries\FolderManager::createShotFolders($project->project_code, $sequence->name, $this->request->getPost('shot_number'));
        }

        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Shot added successfully.');
    }

    public function updateSequence($id)
    {
        $projectId = $this->request->getPost('project_id');
        $model = new \App\Models\SequenceModel();
        
        $model->update($id, [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ]);
        
        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Sequence updated successfully.');
    }

    public function deleteSequence($id)
    {
        $projectId = $this->request->getPost('project_id');
        $model = new \App\Models\SequenceModel();
        $shotModel = new \App\Models\ShotModel();
        
        // Unlink shots
        $shotModel->where('sequence_id', $id)->set(['sequence_id' => null])->update();
        
        $model->delete($id);
        
        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Sequence deleted and its shots were unlinked.');
    }

    public function updateShot($id)
    {
        $projectId = $this->request->getPost('project_id');
        $model = new \App\Models\ShotModel();
        
        $data = [
            'shot_number' => $this->request->getPost('shot_number'),
            'description' => $this->request->getPost('description'),
            'fps'         => $this->request->getPost('fps') ? (int)$this->request->getPost('fps') : null,
            'frame_count' => $this->request->getPost('frame_count') ? (int)$this->request->getPost('frame_count') : null,
        ];
        
        $sequenceId = $this->request->getPost('sequence_id');
        $data['sequence_id'] = empty($sequenceId) ? null : $sequenceId;
        
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/shots', $newName);
            $data['thumbnail_path'] = 'uploads/shots/' . $newName;
        }
        
        $model->update($id, $data);
        
        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Shot updated successfully.');
    }

    public function deleteShot($id)
    {
        $projectId = $this->request->getPost('project_id');
        $model = new \App\Models\ShotModel();
        
        $model->delete($id);
        
        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Shot deleted successfully.');
    }

    public function storeAsset()
    {
        $projectId = $this->request->getPost('project_id');
        $rules = [
            'project_id' => 'required|is_natural_no_zero',
            'name'       => 'required|max_length[150]',
            'type'       => 'required|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/projects/' . $projectId)->with('error', 'Validation failed for Asset.');
        }

        $model = new \App\Models\AssetModel();
        
        $thumbnailPath = null;
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/assets', $newName);
            $thumbnailPath = 'uploads/assets/' . $newName;
        }

        $model->insert([
            'project_id'     => $projectId,
            'name'           => $this->request->getPost('name'),
            'type'           => $this->request->getPost('type'),
            'description'    => $this->request->getPost('description'),
            'thumbnail_path' => $thumbnailPath,
        ]);

        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);
        \App\Libraries\FolderManager::createAssetFolders($project->project_code, $this->request->getPost('type'), $this->request->getPost('name'));

        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Asset added successfully.');
    }

    public function storeBenchmarks()
    {
        $projectId = $this->request->getPost('project_id');
        if (!$projectId) return redirect()->back()->with('error', 'Missing project ID.');

        $benchmarkModel = new \App\Models\TaskBenchmarkModel();
        
        $benchmarks = $this->request->getPost('benchmarks');
        if (is_array($benchmarks)) {
            foreach ($benchmarks as $taskTypeId => $hours) {
                // Check if benchmark exists
                $existing = $benchmarkModel->where('project_id', $projectId)
                                           ->where('task_type_id', $taskTypeId)
                                           ->first();
                $data = [
                    'project_id'    => $projectId,
                    'task_type_id'  => $taskTypeId,
                    'simple_hours'  => isset($hours['simple']) ? (float)$hours['simple'] : 0,
                    'medium_hours'  => isset($hours['medium']) ? (float)$hours['medium'] : 0,
                    'complex_hours' => isset($hours['complex']) ? (float)$hours['complex'] : 0,
                ];

                if ($existing) {
                    $benchmarkModel->update($existing->id, $data);
                } else {
                    $benchmarkModel->insert($data);
                }
            }
        }

        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Benchmarks saved successfully.');
    }

    public function updateSettings($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new \App\Models\ProjectModel();
        $project = $model->find($id);

        if (!$project) {
            return redirect()->back()->with('error', 'Project not found.');
        }

        $data = [];
        if ($this->request->getPost('fps') !== null) {
            $data['fps'] = (int)$this->request->getPost('fps');
        }
        if ($this->request->getPost('status') !== null) {
            $data['status'] = $this->request->getPost('status');
        }
        if ($this->request->getPost('priority') !== null) {
            $data['priority'] = $this->request->getPost('priority');
        }

        if (!empty($data)) {
            $model->update($id, $data);
        }

        $returnUrl = $this->request->getPost('return_url');
        if ($returnUrl) {
            return redirect()->to($returnUrl)->with('message', 'Project updated.');
        }

        return redirect()->back()->with('message', 'Project updated.');
    }
}
