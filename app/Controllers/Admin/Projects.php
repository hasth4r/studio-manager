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

        // Dynamic Scope Filtering: 
        // 1. Site Manager / Admin: sees ALL studio projects.
        // 2. Project Manager / Supervisor: sees ONLY projects where they are designated project supervisor or sequence supervisor.
        // 3. Artist / Freelancer / Collaborator: sees ONLY projects where they are actively assigned tasks or collaborating.
        $userId = (int)session()->get('userId');
        if (!has_any_role(['site_manager', 'admin'])) {
            $supervisedProj = $db->table('projects')->select('id')->where('supervisor_id', $userId)->get()->getResultArray();
            $supervisedSeq = $db->table('sequences')->select('project_id')->where('supervisor_id', $userId)->get()->getResultArray();
            
            $allowedIds = array_values(array_unique(array_filter(array_merge(
                array_column($supervisedProj, 'id'),
                array_column($supervisedSeq, 'project_id')
            ))));

            // If user is not supervising any project/seq, check if they have assigned tasks
            if (empty($allowedIds)) {
                $assignedTasks = $db->table('tasks')->select('project_id')->where('assigned_to', $userId)->get()->getResultArray();
                $allowedIds = array_values(array_unique(array_filter(array_column($assignedTasks, 'project_id'))));
            }

            if (!empty($allowedIds)) {
                $builder->whereIn('projects.id', $allowedIds);
            } else {
                $builder->where('projects.id', -1);
            }
        }

        $projects = $builder->get()->getResult();

        $settingsModel = new \App\Models\SettingsModel();
        $studioCurrency = $settingsModel->getSetting('studio_currency', '₹');
        $opsHourlyRate = (float)$settingsModel->getSetting('studio_ops_hourly_rate', 100.00);
        $commissionPct = (float)$settingsModel->getSetting('studio_commission_pct', 30.0);
        $defaultArtistRate = (float)$settingsModel->getSetting('default_artist_rate', 500.00);

        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();
        $userRateMap = [];
        foreach ($users as $u) {
            $userRateMap[$u->id] = (float)($u->hourly_rate ?? $defaultArtistRate);
        }

        foreach ($projects as $proj) {
            $tasks = $db->table('tasks')->where('project_id', $proj->id)->get()->getResult();
            $projHours = 0.0;
            $projIdealBudget = 0.0;
            foreach ($tasks as $t) {
                $h = (float)($t->estimated_hours ?? 0);
                $r = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
                $b = ($h * $r + $h * $opsHourlyRate) * (1 + ($commissionPct / 100.0));
                $projHours += $h;
                $projIdealBudget += $b;
            }
            $proj->total_hours = round($projHours, 1);
            $proj->ideal_budget = round($projIdealBudget, 0);
            $agreed = (float)($proj->agreed_budget ?? 0);
            $proj->scale_percent = ($agreed > 0 && $projIdealBudget > 0) ? round(($agreed / $projIdealBudget) * 100, 1) : 100.0;
        }

        $data = [
            'pageTitle'      => 'Projects',
            'projects'       => $projects,
            'studioCurrency' => $studioCurrency,
        ];

        return view('projects/index', $data);
    }

    public function create()
    {
        if (!has_any_role(['site_manager', 'admin', 'project_manager'])) {
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
        if (!has_any_role(['site_manager', 'admin', 'project_manager'])) {
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
        if (!has_any_role(['site_manager', 'admin', 'project_manager'])) {
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

        // Project-Level Access Scope Guard
        // Non-admins can only view projects they supervise or have tasks in
        $currentUserId = (int)session()->get('userId');
        if (!has_any_role(['site_manager', 'admin'])) {
            $isSupervisor = (int)$project->supervisor_id === $currentUserId;
            $isSeqSupervisor = $db->table('sequences')->where('project_id', $id)->where('supervisor_id', $currentUserId)->countAllResults() > 0;
            $hasTask = $db->table('tasks')->where('project_id', $id)->where('assigned_to', $currentUserId)->countAllResults() > 0;

            if (!$isSupervisor && !$isSeqSupervisor && !$hasTask) {
                return redirect()->to('/admin/projects')->with('error', 'You do not have access to this project.');
            }
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

        // Economics & Budgeting Data
        $settingsModel = new \App\Models\SettingsModel();
        $studioCurrency = $settingsModel->getSetting('studio_currency', '₹');
        $opsHourlyRate = (float)$settingsModel->getSetting('studio_ops_hourly_rate', 100.00);
        $commissionPct = (float)$settingsModel->getSetting('studio_commission_pct', 30.0);
        $defaultArtistRate = (float)$settingsModel->getSetting('default_artist_rate', 500.00);

        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();
        $userRateMap = [];
        foreach ($users as $u) {
            $userRateMap[$u->id] = (float)($u->hourly_rate ?? $defaultArtistRate);
        }

        $totalProjectHours = 0.0;
        $totalIdealBudget = 0.0;
        foreach ($tasks as $t) {
            $h = (float)($t->estimated_hours ?? 0);
            $r = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
            $b = ($h * $r + $h * $opsHourlyRate) * (1 + ($commissionPct / 100.0));
            $totalProjectHours += $h;
            $totalIdealBudget += $b;
        }

        $agreedBudget = (float)($project->agreed_budget ?? 0);
        $scalePercent = ($agreedBudget > 0 && $totalIdealBudget > 0) ? round(($agreedBudget / $totalIdealBudget) * 100, 1) : 100.0;

        // Benchmarks Data
        $taskTypeModel = new \App\Models\TaskTypeModel();
        $benchmarkModel = new \App\Models\TaskBenchmarkModel();
        $taskTypes = $taskTypeModel->orderBy('name', 'ASC')->findAll();
        $benchmarks = $benchmarkModel->getProjectBenchmarks($id);

        $data = [
            'pageTitle'          => 'Project: ' . $project->name,
            'project'            => $project,
            'users'              => $users,
            'sequences'          => $sequences,
            'shots'              => $shots,
            'assets'             => $assets,
            'taskTypes'          => $taskTypes,
            'benchmarks'         => $benchmarks,
            'totalProjectHours'  => round($totalProjectHours, 1),
            'totalIdealBudget'   => round($totalIdealBudget, 0),
            'agreedBudget'       => round($agreedBudget, 0),
            'scalePercent'       => $scalePercent,
            'studioCurrency'     => $studioCurrency,
            'analytics'          => [
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

            // Auto-recalculate tasks for this project with new benchmarks
            $taskModel = new \App\Models\TaskModel();
            $tasks = $taskModel->where('project_id', $projectId)->findAll();
            foreach ($tasks as $t) {
                $taskModel->update($t->id, [
                    'complexity' => $t->complexity ?: 'Medium',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return redirect()->to('/admin/projects/' . $projectId)->with('message', 'Benchmarks saved & tasks recalculated.');
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

    public function importShots($projectId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Prevent timeouts and memory exhaustion for large batches (e.g. 200+ shots)
        @ini_set('max_execution_time', '600');
        @ini_set('memory_limit', '512M');
        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }

        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);

        if (!$project) {
            return redirect()->back()->with('error', 'Project not found.');
        }

        $limit = (int)$this->request->getPost('limit');
        $autoCreateFolders = (bool)$this->request->getPost('auto_create_folders');
        $folderPath = trim($this->request->getPost('folder_path') ?? '');
        $uploadedCsv = $this->request->getFile('csv_file');
        $uploadedThumbs = $this->request->getFiles()['thumbnails'] ?? [];
        $uploadedVideos = $this->request->getFiles()['video_previews'] ?? [];

        $rows = [];
        $localThumbDir = null;
        $localVideoDir = null;
        $tempExtractPath = null;

        // Ensure upload destinations exist
        $targetThumbDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'shots';
        if (!is_dir($targetThumbDir)) {
            @mkdir($targetThumbDir, 0777, true);
        }

        $targetVideoDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'shots' . DIRECTORY_SEPARATOR . 'videos';
        if (!is_dir($targetVideoDir)) {
            @mkdir($targetVideoDir, 0777, true);
        }

        // 1. Process from Local Folder Path (AE Export)
        if (!empty($folderPath)) {
            $folderPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $folderPath), DIRECTORY_SEPARATOR);
            if (!is_dir($folderPath)) {
                return redirect()->back()->with('error', 'Export folder path not found: ' . esc($folderPath));
            }

            // Find CSV or JSON file in the export folder
            $csvFiles = glob($folderPath . DIRECTORY_SEPARATOR . '*.csv');
            $jsonFiles = glob($folderPath . DIRECTORY_SEPARATOR . '*.json');
            
            // Look for thumbnails and videos directories
            $thumbDirCandidate = $folderPath . DIRECTORY_SEPARATOR . 'thumbnails';
            $localThumbDir = is_dir($thumbDirCandidate) ? $thumbDirCandidate : $folderPath;

            $videoDirCandidate = $folderPath . DIRECTORY_SEPARATOR . 'videos';
            if (!is_dir($videoDirCandidate)) {
                $videoDirCandidate = $folderPath . DIRECTORY_SEPARATOR . 'previews';
            }
            $localVideoDir = is_dir($videoDirCandidate) ? $videoDirCandidate : $folderPath;

            if (!empty($csvFiles)) {
                // Prefer files with 'shotlist' or 'metadata' in name
                $targetFile = $csvFiles[0];
                foreach ($csvFiles as $f) {
                    if (stripos($f, 'shotlist') !== false || stripos($f, 'metadata') !== false) {
                        $targetFile = $f;
                        break;
                    }
                }
                $rows = $this->parseCsvFile($targetFile);
            } elseif (!empty($jsonFiles)) {
                $targetFile = $jsonFiles[0];
                $jsonContent = @file_get_contents($targetFile);
                $jsonData = json_decode($jsonContent, true);
                if (is_array($jsonData)) {
                    $rows = $jsonData;
                }
            } else {
                return redirect()->back()->with('error', 'No .csv or .json metadata files found in ' . esc($folderPath));
            }
        }
        // 2. Process from Uploaded CSV / ZIP
        elseif ($uploadedCsv && $uploadedCsv->isValid() && !$uploadedCsv->hasMoved()) {
            $ext = strtolower($uploadedCsv->getClientExtension());
            if ($ext === 'csv' || $ext === 'txt') {
                $rows = $this->parseCsvFile($uploadedCsv->getTempName());
            } elseif ($ext === 'zip') {
                if (!class_exists('ZipArchive')) {
                    return redirect()->back()->with('error', 'PHP ZipArchive extension is not enabled on this server.');
                }
                $zip = new \ZipArchive();
                $tempExtractPath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'temp_zip_' . uniqid();
                if ($zip->open($uploadedCsv->getTempName()) === true) {
                    $zip->extractTo($tempExtractPath);
                    $zip->close();

                    // Recursively find CSV, thumbnails, and video directories inside ZIP
                    $csvFiles = [];
                    $dirIterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($tempExtractPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );

                    foreach ($dirIterator as $item) {
                        if ($item->isFile() && in_array(strtolower($item->getExtension()), ['csv', 'txt'])) {
                            $csvFiles[] = $item->getPathname();
                        }
                        if ($item->isDir()) {
                            $dirNameLower = strtolower($item->getFilename());
                            if ($dirNameLower === 'thumbnails' || $dirNameLower === 'thumbs') {
                                $localThumbDir = $item->getPathname();
                            } elseif (in_array($dirNameLower, ['videos', 'previews', 'video', 'preview'])) {
                                $localVideoDir = $item->getPathname();
                            }
                        }
                    }

                    if (!empty($csvFiles)) {
                        $targetCsv = $csvFiles[0];
                        foreach ($csvFiles as $f) {
                            if (stripos($f, 'shotlist') !== false || stripos($f, 'metadata') !== false) {
                                $targetCsv = $f;
                                break;
                            }
                        }
                        $rows = $this->parseCsvFile($targetCsv);
                        if (empty($localThumbDir)) {
                            $localThumbDir = dirname($targetCsv);
                        }
                        if (empty($localVideoDir)) {
                            $localVideoDir = dirname($targetCsv);
                        }
                    } else {
                        // Direct Media-Only ZIP Update Mode (No CSV required!)
                        $extractedMedia = [];
                        foreach ($dirIterator as $item) {
                            if ($item->isFile()) {
                                $ext = strtolower($item->getExtension());
                                if (in_array($ext, ['mp4', 'mov', 'webm', 'm4v', 'png', 'jpg', 'jpeg'])) {
                                    $extractedMedia[] = $item->getPathname();
                                }
                            }
                        }

                        if (!empty($extractedMedia)) {
                            $shotModel = new \App\Models\ShotModel();
                            $existingShots = $shotModel->where('project_id', $projectId)->findAll();
                            $matchedVideos = 0;
                            $matchedThumbs = 0;

                            foreach ($extractedMedia as $mediaPath) {
                                $mFilename = pathinfo($mediaPath, PATHINFO_FILENAME);
                                $mExt = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                                $isVideo = in_array($mExt, ['mp4', 'mov', 'webm', 'm4v']);

                                foreach ($existingShots as $eshot) {
                                    $shotNumClean = strtolower(trim($eshot->shot_number));
                                    $compNameClean = strtolower(trim($eshot->comp_name ?? ''));
                                    $mLower = strtolower($mFilename);

                                    $isMatch = ($mLower === $shotNumClean)
                                        || (!empty($compNameClean) && $mLower === $compNameClean)
                                        || (strpos($mLower, $shotNumClean) !== false && strlen($shotNumClean) >= 3);

                                    if ($isMatch) {
                                        if ($isVideo) {
                                            $newVidName = 'vid_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $eshot->shot_number) . '_' . uniqid() . '.' . $mExt;
                                            @copy($mediaPath, $targetVideoDir . DIRECTORY_SEPARATOR . $newVidName);
                                            $shotModel->update($eshot->id, ['preview_video_path' => 'uploads/shots/videos/' . $newVidName]);
                                            $this->syncMediaToR2($targetVideoDir . DIRECTORY_SEPARATOR . $newVidName, 'uploads/shots/videos/' . $newVidName);
                                            $matchedVideos++;
                                        } else {
                                            $newThumbName = 'shot_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $eshot->shot_number) . '_' . uniqid() . '.' . $mExt;
                                            @copy($mediaPath, $targetThumbDir . DIRECTORY_SEPARATOR . $newThumbName);
                                            $shotModel->update($eshot->id, ['thumbnail_path' => 'uploads/shots/' . $newThumbName]);
                                            $this->syncMediaToR2($targetThumbDir . DIRECTORY_SEPARATOR . $newThumbName, 'uploads/shots/' . $newThumbName);
                                            $matchedThumbs++;
                                        }
                                        break;
                                    }
                                }
                            }

                            $mediaMsg = "Media update complete: Linked {$matchedVideos} preview videos and {$matchedThumbs} thumbnails to your existing shots!";
                            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                                return $this->response->setJSON([
                                    'success' => true,
                                    'message' => $mediaMsg,
                                    'redirect' => '/admin/projects/' . $projectId
                                ]);
                            }
                            return redirect()->to('/admin/projects/' . $projectId)->with('message', $mediaMsg);
                        } else {
                            $err = 'No CSV or media files found inside the uploaded ZIP archive.';
                            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                                return $this->response->setJSON(['success' => false, 'error' => $err])->setStatusCode(400);
                            }
                            return redirect()->back()->with('error', $err);
                        }
                    }
                } else {
                    $err = 'Failed to extract uploaded ZIP file.';
                    if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                        return $this->response->setJSON(['success' => false, 'error' => $err])->setStatusCode(400);
                    }
                    return redirect()->back()->with('error', $err);
                }
            } else {
                $err = 'Unsupported file format. Please upload a .csv or .zip file.';
                if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                    return $this->response->setJSON(['success' => false, 'error' => $err])->setStatusCode(400);
                }
                return redirect()->back()->with('error', $err);
            }
        } else {
            $err = 'Please provide an AE export folder path or upload a CSV/ZIP file.';
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON(['success' => false, 'error' => $err])->setStatusCode(400);
            }
            return redirect()->back()->with('error', $err);
        }

        if (empty($rows)) {
            $err = 'No valid shot rows could be parsed from the import source.';
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON(['success' => false, 'error' => $err])->setStatusCode(400);
            }
            return redirect()->back()->with('error', $err);
        }

        // Apply limit if specified (e.g. testing 5 shots)
        if ($limit > 0 && count($rows) > $limit) {
            $rows = array_slice($rows, 0, $limit);
        }

        // Build index of uploaded thumbnail and video files
        $uploadedFileMap = [];
        if (!empty($uploadedThumbs)) {
            foreach ($uploadedThumbs as $file) {
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $origName = strtolower($file->getClientName());
                    $baseNoExt = strtolower(pathinfo($origName, PATHINFO_FILENAME));
                    $uploadedFileMap[$origName] = $file;
                    $uploadedFileMap[$baseNoExt] = $file;
                }
            }
        }

        $uploadedVideoMap = [];
        if (!empty($uploadedVideos)) {
            foreach ($uploadedVideos as $vfile) {
                if ($vfile && $vfile->isValid() && !$vfile->hasMoved()) {
                    $origName = strtolower($vfile->getClientName());
                    $baseNoExt = strtolower(pathinfo($origName, PATHINFO_FILENAME));
                    $uploadedVideoMap[$origName] = $vfile;
                    $uploadedVideoMap[$baseNoExt] = $vfile;
                }
            }
        }

        // Prepare Models & Caches
        $sequenceModel = new \App\Models\SequenceModel();
        $shotModel = new \App\Models\ShotModel();
        
        $existingSequences = $sequenceModel->where('project_id', $projectId)->findAll();
        $seqMap = [];
        foreach ($existingSequences as $seq) {
            $seqMap[strtolower(trim($seq->name))] = $seq->id;
        }

        $importedCount = 0;
        $updatedCount  = 0;
        $thumbCount    = 0;
        $videoCount    = 0;
        $createdSeqs   = 0;

        foreach ($rows as $row) {
            // Normalize keys to lowercase trimmed
            $cleanRow = [];
            foreach ($row as $k => $v) {
                $cleanRow[strtolower(trim($k))] = is_string($v) ? trim($v) : $v;
            }

            // Extract Shot Number / Identifier
            $shotNumber = $cleanRow['shot'] ?? $cleanRow['shot_number'] ?? $cleanRow['shot_code'] ?? $cleanRow['name'] ?? null;
            if (empty($shotNumber)) {
                continue;
            }

            // Extract Sequence
            $seqName = $cleanRow['sequence'] ?? $cleanRow['sequence_name'] ?? $cleanRow['seq'] ?? $cleanRow['seq_name'] ?? null;
            $sequenceId = null;
            if (!empty($seqName)) {
                $seqKey = strtolower(trim($seqName));
                if (isset($seqMap[$seqKey])) {
                    $sequenceId = $seqMap[$seqKey];
                } else {
                    $sequenceId = $sequenceModel->insert([
                        'project_id'  => $projectId,
                        'name'        => $seqName,
                        'description' => 'Imported via bulk shot import'
                    ]);
                    $seqMap[$seqKey] = $sequenceId;
                    $createdSeqs++;
                }
            }

            // Extract Metadata & Pipeline Fields
            $frameCount = $cleanRow['duration_frames'] ?? $cleanRow['frame_count'] ?? $cleanRow['frames'] ?? $cleanRow['duration'] ?? null;
            $fps = $cleanRow['fps'] ?? $cleanRow['frame_rate'] ?? ($project->fps ?? 24);
            $description = $cleanRow['description'] ?? null;
            $compName = $cleanRow['comp_name'] ?? null;
            $frameIn = $cleanRow['frame_in'] ?? null;
            $frameOut = $cleanRow['frame_out'] ?? null;
            $durationSec = $cleanRow['duration_sec'] ?? $cleanRow['duration_seconds'] ?? null;
            $timecodeIn = $cleanRow['timecode_in'] ?? null;
            $timecodeOut = $cleanRow['timecode_out'] ?? null;
            $width = $cleanRow['width'] ?? null;
            $height = $cleanRow['height'] ?? null;

            // 1. Resolve Thumbnail
            $thumbFileRef = $cleanRow['thumbnail_file'] ?? $cleanRow['thumbnail'] ?? $cleanRow['thumb'] ?? null;
            $savedThumbPath = null;

            // A. Check local thumb directory
            if (!empty($localThumbDir)) {
                $candidateFiles = [];
                if (!empty($thumbFileRef)) {
                    $baseThumb = basename($thumbFileRef);
                    $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . $baseThumb;
                    $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . pathinfo($baseThumb, PATHINFO_FILENAME) . '.png';
                    $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . pathinfo($baseThumb, PATHINFO_FILENAME) . '.jpg';
                    $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . pathinfo($baseThumb, PATHINFO_FILENAME) . '.jpeg';
                }
                // Fallback: search by shot number or comp name
                $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . $shotNumber . '.png';
                $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . $shotNumber . '.jpg';
                if (!empty($cleanRow['comp_name'])) {
                    $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . $cleanRow['comp_name'] . '.png';
                    $candidateFiles[] = $localThumbDir . DIRECTORY_SEPARATOR . $cleanRow['comp_name'] . '.jpg';
                }

                foreach ($candidateFiles as $candidate) {
                    if (file_exists($candidate) && is_file($candidate)) {
                        $ext = pathinfo($candidate, PATHINFO_EXTENSION);
                        $newFilename = 'shot_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $shotNumber) . '_' . uniqid() . '.' . $ext;
                        $destPath = $targetThumbDir . DIRECTORY_SEPARATOR . $newFilename;
                        if (@copy($candidate, $destPath)) {
                            $savedThumbPath = 'uploads/shots/' . $newFilename;
                            $thumbCount++;
                            break;
                        }
                    }
                }
            }

            // B. Check uploaded files index
            if (empty($savedThumbPath) && !empty($uploadedFileMap)) {
                $checkKeys = [];
                if (!empty($thumbFileRef)) {
                    $base = strtolower(basename($thumbFileRef));
                    $checkKeys[] = $base;
                    $checkKeys[] = pathinfo($base, PATHINFO_FILENAME);
                }
                $checkKeys[] = strtolower($shotNumber);
                if (!empty($cleanRow['comp_name'])) {
                    $checkKeys[] = strtolower($cleanRow['comp_name']);
                }

                foreach ($checkKeys as $chk) {
                    if (isset($uploadedFileMap[$chk])) {
                        $upFile = $uploadedFileMap[$chk];
                        $newFilename = $upFile->getRandomName();
                        $upFile->move($targetThumbDir, $newFilename);
                        $savedThumbPath = 'uploads/shots/' . $newFilename;
                        $thumbCount++;
                        break;
                    }
                }
            }

            // 2. Resolve Preview Video
            $videoFileRef = $cleanRow['video_file'] ?? $cleanRow['video'] ?? $cleanRow['preview_video'] ?? $cleanRow['video_preview'] ?? null;
            $savedVideoPath = null;

            // A. Check local video directory / ZIP
            if (!empty($localVideoDir)) {
                $candidateVideos = [];
                if (!empty($videoFileRef)) {
                    $baseVid = basename($videoFileRef);
                    $candidateVideos[] = $localVideoDir . DIRECTORY_SEPARATOR . $baseVid;
                }
                $candidateVideos[] = $localVideoDir . DIRECTORY_SEPARATOR . $shotNumber . '.mp4';
                $candidateVideos[] = $localVideoDir . DIRECTORY_SEPARATOR . $shotNumber . '.mov';
                $candidateVideos[] = $localVideoDir . DIRECTORY_SEPARATOR . $shotNumber . '.webm';
                if (!empty($cleanRow['comp_name'])) {
                    $candidateVideos[] = $localVideoDir . DIRECTORY_SEPARATOR . $cleanRow['comp_name'] . '.mp4';
                    $candidateVideos[] = $localVideoDir . DIRECTORY_SEPARATOR . $cleanRow['comp_name'] . '.mov';
                    $candidateVideos[] = $localVideoDir . DIRECTORY_SEPARATOR . $cleanRow['comp_name'] . '.webm';
                }

                foreach ($candidateVideos as $candVid) {
                    if (file_exists($candVid) && is_file($candVid)) {
                        $ext = pathinfo($candVid, PATHINFO_EXTENSION);
                        $newVidName = 'vid_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $shotNumber) . '_' . uniqid() . '.' . $ext;
                        $destVidPath = $targetVideoDir . DIRECTORY_SEPARATOR . $newVidName;
                        if (@copy($candVid, $destVidPath)) {
                            $savedVideoPath = 'uploads/shots/videos/' . $newVidName;
                            $videoCount++;
                            break;
                        }
                    }
                }
            }

            // B. Check uploaded videos index
            if (empty($savedVideoPath) && !empty($uploadedVideoMap)) {
                $checkVidKeys = [];
                if (!empty($videoFileRef)) {
                    $vbase = strtolower(basename($videoFileRef));
                    $checkVidKeys[] = $vbase;
                    $checkVidKeys[] = pathinfo($vbase, PATHINFO_FILENAME);
                }
                $checkVidKeys[] = strtolower($shotNumber);
                if (!empty($cleanRow['comp_name'])) {
                    $checkVidKeys[] = strtolower($cleanRow['comp_name']);
                }

                foreach ($checkVidKeys as $vchk) {
                    if (isset($uploadedVideoMap[$vchk])) {
                        $upVid = $uploadedVideoMap[$vchk];
                        $newVidName = $upVid->getRandomName();
                        $upVid->move($targetVideoDir, $newVidName);
                        $savedVideoPath = 'uploads/shots/videos/' . $newVidName;
                        $videoCount++;
                        break;
                    }
                }
            }

            // Check if Shot already exists in this project
            $query = $shotModel->where('project_id', $projectId)->where('shot_number', $shotNumber);
            if ($sequenceId) {
                $query->where('sequence_id', $sequenceId);
            }
            $existingShot = $query->first();

            $shotData = [
                'project_id'       => $projectId,
                'sequence_id'      => $sequenceId,
                'shot_number'      => $shotNumber,
                'comp_name'        => !empty($compName) ? $compName : null,
                'fps'              => !empty($fps) ? (int)$fps : null,
                'frame_count'      => !empty($frameCount) ? (int)$frameCount : null,
                'frame_in'         => !empty($frameIn) ? (int)$frameIn : null,
                'frame_out'        => !empty($frameOut) ? (int)$frameOut : null,
                'duration_seconds' => !empty($durationSec) ? (float)$durationSec : null,
                'timecode_in'      => !empty($timecodeIn) ? $timecodeIn : null,
                'timecode_out'     => !empty($timecodeOut) ? $timecodeOut : null,
                'width'            => !empty($width) ? (int)$width : null,
                'height'           => !empty($height) ? (int)$height : null,
            ];
            if (!empty($description)) {
                $shotData['description'] = $description;
            }
            if (!empty($savedThumbPath)) {
                $shotData['thumbnail_path'] = $savedThumbPath;
            }
            if (!empty($savedVideoPath)) {
                $shotData['preview_video_path'] = $savedVideoPath;
            }

            if ($existingShot) {
                $shotModel->update($existingShot->id, $shotData);
                $updatedCount++;
            } else {
                $shotModel->insert($shotData);
                $importedCount++;
            }

            // Create folders on disk if enabled
            if ($autoCreateFolders && !empty($seqName)) {
                \App\Libraries\FolderManager::createShotFolders($project->project_code, $seqName, $shotNumber);
            }
        }

        $summary = "Import completed: {$importedCount} new shots added, {$updatedCount} updated, {$createdSeqs} new sequences created, {$thumbCount} thumbnails linked, and {$videoCount} preview videos linked.";
        if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => $summary,
                'redirect' => '/admin/projects/' . $projectId
            ]);
        }
        return redirect()->to('/admin/projects/' . $projectId)->with('message', $summary);
    }

    /**
     * Chunked ZIP / Video Upload Handler
     * Slices large uploads into 5MB chunks to prevent server execution timeouts.
     */
    public function chunkUpload($projectId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Authentication required'])->setStatusCode(401);
        }

        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->request->getPost('upload_id') ?? '');
        $chunkIndex = (int)($this->request->getPost('chunk_index') ?? 0);
        $totalChunks = (int)($this->request->getPost('total_chunks') ?? 1);
        $originalName = $this->request->getPost('file_name') ?? 'upload.zip';
        $limit = (int)($this->request->getPost('limit') ?? 0);
        $autoCreateFolders = (bool)($this->request->getPost('auto_create_folders') ?? false);

        if (empty($uploadId)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Missing upload session ID'])->setStatusCode(400);
        }

        $chunkFile = $this->request->getFile('file_chunk');
        if (!$chunkFile || !$chunkFile->isValid()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid chunk file'])->setStatusCode(400);
        }

        $tempDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'chunks' . DIRECTORY_SEPARATOR . $uploadId;
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $chunkPath = $tempDir . DIRECTORY_SEPARATOR . 'chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT);
        $chunkFile->move($tempDir, basename($chunkPath));

        // If more chunks remain, acknowledge receipt
        if ($chunkIndex < $totalChunks - 1) {
            return $this->response->setJSON([
                'success' => true,
                'chunk_index' => $chunkIndex,
                'status' => 'chunk_received'
            ]);
        }

        // All chunks received! Assemble complete file
        $finalFilePath = $tempDir . DIRECTORY_SEPARATOR . 'combined_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $outFp = fopen($finalFilePath, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $partPath = $tempDir . DIRECTORY_SEPARATOR . 'chunk_' . str_pad($i, 5, '0', STR_PAD_LEFT);
            if (!file_exists($partPath)) {
                fclose($outFp);
                return $this->response->setJSON(['success' => false, 'error' => "Missing chunk {$i} during file assembly."])->setStatusCode(400);
            }
            $inFp = fopen($partPath, 'rb');
            while (!feof($inFp)) {
                fwrite($outFp, fread($inFp, 1048576));
            }
            fclose($inFp);
            @unlink($partPath);
        }
        fclose($outFp);

        // Process the combined file
        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);
        if (!$project) {
            @unlink($finalFilePath);
            @rmdir($tempDir);
            return $this->response->setJSON(['success' => false, 'error' => 'Project not found'])->setStatusCode(404);
        }

        $targetThumbDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'shots';
        $targetVideoDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'shots' . DIRECTORY_SEPARATOR . 'videos';
        if (!is_dir($targetThumbDir)) @mkdir($targetThumbDir, 0777, true);
        if (!is_dir($targetVideoDir)) @mkdir($targetVideoDir, 0777, true);

        $zip = new \ZipArchive();
        $tempExtractPath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'temp_zip_' . uniqid();
        if ($zip->open($finalFilePath) === true) {
            $zip->extractTo($tempExtractPath);
            $zip->close();

            $dirIterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempExtractPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $csvFiles = [];
            $localThumbDir = null;
            $localVideoDir = null;

            foreach ($dirIterator as $item) {
                if ($item->isFile() && in_array(strtolower($item->getExtension()), ['csv', 'txt'])) {
                    $csvFiles[] = $item->getPathname();
                }
                if ($item->isDir()) {
                    $dirNameLower = strtolower($item->getFilename());
                    if ($dirNameLower === 'thumbnails' || $dirNameLower === 'thumbs') {
                        $localThumbDir = $item->getPathname();
                    } elseif (in_array($dirNameLower, ['videos', 'previews', 'video', 'preview'])) {
                        $localVideoDir = $item->getPathname();
                    }
                }
            }

            if (!empty($csvFiles)) {
                $targetCsv = $csvFiles[0];
                foreach ($csvFiles as $f) {
                    if (stripos($f, 'shotlist') !== false || stripos($f, 'metadata') !== false) {
                        $targetCsv = $f;
                        break;
                    }
                }
                $rows = $this->parseCsvFile($targetCsv);
                if (empty($localThumbDir)) $localThumbDir = dirname($targetCsv);
                if (empty($localVideoDir)) $localVideoDir = dirname($targetCsv);

                if ($limit > 0 && count($rows) > $limit) {
                    $rows = array_slice($rows, 0, $limit);
                }

                $sequenceModel = new \App\Models\SequenceModel();
                $shotModel = new \App\Models\ShotModel();
                $existingSequences = $sequenceModel->where('project_id', $projectId)->findAll();
                $seqMap = [];
                foreach ($existingSequences as $seq) {
                    $seqMap[strtolower(trim($seq->name))] = $seq->id;
                }

                $importedCount = 0; $updatedCount = 0; $thumbCount = 0; $videoCount = 0; $createdSeqs = 0;

                foreach ($rows as $row) {
                    $cleanRow = [];
                    foreach ($row as $k => $v) {
                        $cleanRow[strtolower(trim($k))] = is_string($v) ? trim($v) : $v;
                    }
                    $shotNumber = $cleanRow['shot'] ?? $cleanRow['shot_number'] ?? $cleanRow['shot_code'] ?? $cleanRow['name'] ?? null;
                    if (empty($shotNumber)) continue;

                    $seqName = $cleanRow['sequence'] ?? $cleanRow['sequence_name'] ?? $cleanRow['seq'] ?? null;
                    $sequenceId = null;
                    if (!empty($seqName)) {
                        $seqKey = strtolower(trim($seqName));
                        if (isset($seqMap[$seqKey])) {
                            $sequenceId = $seqMap[$seqKey];
                        } else {
                            $sequenceId = $sequenceModel->insert(['project_id' => $projectId, 'name' => $seqName, 'description' => 'Bulk imported']);
                            $seqMap[$seqKey] = $sequenceId;
                            $createdSeqs++;
                        }
                    }

                    $frameCount = $cleanRow['duration_frames'] ?? $cleanRow['frame_count'] ?? $cleanRow['frames'] ?? null;
                    $fps = $cleanRow['fps'] ?? $cleanRow['frame_rate'] ?? ($project->fps ?? 24);
                    $description = $cleanRow['description'] ?? null;
                    $compName = $cleanRow['comp_name'] ?? null;
                    $frameIn = $cleanRow['frame_in'] ?? null;
                    $frameOut = $cleanRow['frame_out'] ?? null;
                    $durationSec = $cleanRow['duration_seconds'] ?? null;
                    $timecodeIn = $cleanRow['timecode_in'] ?? null;
                    $timecodeOut = $cleanRow['timecode_out'] ?? null;
                    $width = $cleanRow['width'] ?? null;
                    $height = $cleanRow['height'] ?? null;

                    // Match thumbnails
                    $savedThumbPath = null;
                    if (!empty($localThumbDir)) {
                        $candidates = [
                            $localThumbDir . DIRECTORY_SEPARATOR . $shotNumber . '.png',
                            $localThumbDir . DIRECTORY_SEPARATOR . $shotNumber . '.jpg',
                            $localThumbDir . DIRECTORY_SEPARATOR . $shotNumber . '.jpeg',
                        ];
                        foreach ($candidates as $cand) {
                            if (file_exists($cand)) {
                                $ext = pathinfo($cand, PATHINFO_EXTENSION);
                                $newName = 'shot_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $shotNumber) . '_' . uniqid() . '.' . $ext;
                                if (@copy($cand, $targetThumbDir . DIRECTORY_SEPARATOR . $newName)) {
                                    $savedThumbPath = 'uploads/shots/' . $newName;
                                    $thumbCount++;
                                    break;
                                }
                            }
                        }
                    }

                    // Match videos
                    $savedVideoPath = null;
                    if (!empty($localVideoDir)) {
                        $candVids = [
                            $localVideoDir . DIRECTORY_SEPARATOR . $shotNumber . '.mp4',
                            $localVideoDir . DIRECTORY_SEPARATOR . $shotNumber . '.mov',
                            $localVideoDir . DIRECTORY_SEPARATOR . $shotNumber . '.webm',
                        ];
                        if (!empty($compName)) {
                            $candVids[] = $localVideoDir . DIRECTORY_SEPARATOR . $compName . '.mp4';
                            $candVids[] = $localVideoDir . DIRECTORY_SEPARATOR . $compName . '.mov';
                        }
                        foreach ($candVids as $cv) {
                            if (file_exists($cv)) {
                                $ext = pathinfo($cv, PATHINFO_EXTENSION);
                                $newVidName = 'vid_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $shotNumber) . '_' . uniqid() . '.' . $ext;
                                if (@copy($cv, $targetVideoDir . DIRECTORY_SEPARATOR . $newVidName)) {
                                    $savedVideoPath = 'uploads/shots/videos/' . $newVidName;
                                    $videoCount++;
                                    break;
                                }
                            }
                        }
                    }

                    $query = $shotModel->where('project_id', $projectId)->where('shot_number', $shotNumber);
                    if ($sequenceId) $query->where('sequence_id', $sequenceId);
                    $existingShot = $query->first();

                    $shotData = [
                        'project_id'       => $projectId,
                        'sequence_id'      => $sequenceId,
                        'shot_number'      => $shotNumber,
                        'comp_name'        => !empty($compName) ? $compName : null,
                        'fps'              => !empty($fps) ? (int)$fps : null,
                        'frame_count'      => !empty($frameCount) ? (int)$frameCount : null,
                        'frame_in'         => !empty($frameIn) ? (int)$frameIn : null,
                        'frame_out'        => !empty($frameOut) ? (int)$frameOut : null,
                        'duration_seconds' => !empty($durationSec) ? (float)$durationSec : null,
                        'timecode_in'      => !empty($timecodeIn) ? $timecodeIn : null,
                        'timecode_out'     => !empty($timecodeOut) ? $timecodeOut : null,
                        'width'            => !empty($width) ? (int)$width : null,
                        'height'           => !empty($height) ? (int)$height : null,
                    ];
                    if (!empty($description)) $shotData['description'] = $description;
                    if (!empty($savedThumbPath)) $shotData['thumbnail_path'] = $savedThumbPath;
                    if (!empty($savedVideoPath)) $shotData['preview_video_path'] = $savedVideoPath;

                    if ($existingShot) {
                        $shotModel->update($existingShot->id, $shotData);
                        $updatedCount++;
                    } else {
                        $shotModel->insert($shotData);
                        $importedCount++;
                    }
                }

                $msg = "Import completed: {$importedCount} new shots added, {$updatedCount} updated, {$thumbCount} thumbnails linked, and {$videoCount} preview videos linked.";
            } else {
                // Direct Media-Only ZIP Update Mode (No CSV)
                $extractedMedia = [];
                foreach ($dirIterator as $item) {
                    if ($item->isFile()) {
                        $ext = strtolower($item->getExtension());
                        if (in_array($ext, ['mp4', 'mov', 'webm', 'm4v', 'png', 'jpg', 'jpeg'])) {
                            $extractedMedia[] = $item->getPathname();
                        }
                    }
                }

                $shotModel = new \App\Models\ShotModel();
                $existingShots = $shotModel->where('project_id', $projectId)->findAll();
                $matchedVideos = 0;
                $matchedThumbs = 0;

                foreach ($extractedMedia as $mediaPath) {
                    $mFilename = pathinfo($mediaPath, PATHINFO_FILENAME);
                    $mExt = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                    $isVideo = in_array($mExt, ['mp4', 'mov', 'webm', 'm4v']);

                    foreach ($existingShots as $eshot) {
                        $shotNumClean = strtolower(trim($eshot->shot_number));
                        $compNameClean = strtolower(trim($eshot->comp_name ?? ''));
                        $mLower = strtolower($mFilename);

                        $isMatch = ($mLower === $shotNumClean)
                            || (!empty($compNameClean) && $mLower === $compNameClean)
                            || (strpos($mLower, $shotNumClean) !== false && strlen($shotNumClean) >= 3);

                        if ($isMatch) {
                            if ($isVideo) {
                                $newVidName = 'vid_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $eshot->shot_number) . '_' . uniqid() . '.' . $mExt;
                                @copy($mediaPath, $targetVideoDir . DIRECTORY_SEPARATOR . $newVidName);
                                $shotModel->update($eshot->id, ['preview_video_path' => 'uploads/shots/videos/' . $newVidName]);
                                $this->syncMediaToR2($targetVideoDir . DIRECTORY_SEPARATOR . $newVidName, 'uploads/shots/videos/' . $newVidName);
                                $matchedVideos++;
                            } else {
                                $newThumbName = 'shot_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $eshot->shot_number) . '_' . uniqid() . '.' . $mExt;
                                @copy($mediaPath, $targetThumbDir . DIRECTORY_SEPARATOR . $newThumbName);
                                $shotModel->update($eshot->id, ['thumbnail_path' => 'uploads/shots/' . $newThumbName]);
                                $this->syncMediaToR2($targetThumbDir . DIRECTORY_SEPARATOR . $newThumbName, 'uploads/shots/' . $newThumbName);
                                $matchedThumbs++;
                            }
                            break;
                        }
                    }
                }
                $msg = "Media update complete: Linked {$matchedVideos} preview videos and {$matchedThumbs} thumbnails to your existing shots!";
            }

            @unlink($finalFilePath);
            @rmdir($tempDir);

            return $this->response->setJSON([
                'success' => true,
                'message' => $msg,
                'redirect' => '/admin/projects/' . $projectId
            ]);
        } else {
            @unlink($finalFilePath);
            @rmdir($tempDir);
            return $this->response->setJSON(['success' => false, 'error' => 'Failed to extract uploaded ZIP file'])->setStatusCode(400);
        }
    }

    /**
     * AJAX endpoint to save auto-generated WebP thumbnails from client-side video frame capture.
     */
    public function saveAutoThumbnailAjax()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Authentication required'])->setStatusCode(401);
        }

        $shotId = (int)$this->request->getPost('shot_id');
        $imageData = $this->request->getPost('image_data');

        if (!$shotId || empty($imageData)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Missing shot ID or image data'])->setStatusCode(400);
        }

        $shotModel = new \App\Models\ShotModel();
        $shot = $shotModel->find($shotId);
        if (!$shot) {
            return $this->response->setJSON(['success' => false, 'error' => 'Shot not found'])->setStatusCode(404);
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $data = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['webp', 'jpeg', 'jpg', 'png'])) {
                $type = 'webp';
            }
            $decodedData = base64_decode($data);

            if ($decodedData === false) {
                return $this->response->setJSON(['success' => false, 'error' => 'Failed to decode base64 image data'])->setStatusCode(400);
            }

            $project = $this->projectModel->find($shot->project_id);
            $seqModel = new \App\Models\SequenceModel();
            $seq = $shot->sequence_id ? $seqModel->find($shot->sequence_id) : null;
            $pCode = !empty($project->project_code) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $project->project_code) : 'PROJECT_' . $shot->project_id;
            $sName = $seq && !empty($seq->name) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $seq->name) : 'WAR';
            $cleanShotNum = preg_replace('/[^a-zA-Z0-9_-]/', '_', $shot->shot_number);

            $targetThumbDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $pCode . DIRECTORY_SEPARATOR . $sName . DIRECTORY_SEPARATOR . $cleanShotNum . DIRECTORY_SEPARATOR . 'thumbnails';
            if (!is_dir($targetThumbDir)) {
                @mkdir($targetThumbDir, 0777, true);
            }

            $filename = 'shot_' . $cleanShotNum . '.' . $type;
            $filePath = $targetThumbDir . DIRECTORY_SEPARATOR . $filename;

            file_put_contents($filePath, $decodedData);

            $relPath = "uploads/{$pCode}/{$sName}/{$cleanShotNum}/thumbnails/{$filename}";
            $shotModel->update($shotId, ['thumbnail_path' => $relPath]);
            $this->syncMediaToR2($filePath, $relPath);

            return $this->response->setJSON([
                'success' => true,
                'thumbnail_url' => base_url($relPath),
                'shot_id' => $shotId
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Invalid image payload'])->setStatusCode(400);
    }

    /**
     * Optional sync to Cloudflare R2 CDN if configured in .env.
     */
    private function syncMediaToR2($localAbsolutePath, $destRelativePath)
    {
        try {
            $r2 = new \App\Libraries\CloudflareStorage();
            if ($r2->isConfigured()) {
                $r2->uploadFile($localAbsolutePath, $destRelativePath);
            }
        } catch (\Throwable $e) {
            log_message('error', 'R2 Sync Warning: ' . $e->getMessage());
        }
    }

    /**
     * Helper to parse CSV file into associative array.
     */
    private function parseCsvFile($filePath)
    {
        $rows = [];
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return $rows;
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 4096, ',');
            if ($header) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                $header = array_map('trim', $header);

                while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                    if (empty(array_filter($data, fn($val) => $val !== null && trim($val) !== ''))) {
                        continue;
                    }
                    $row = [];
                    foreach ($header as $i => $colName) {
                        $row[$colName] = $data[$i] ?? null;
                    }
                    $rows[] = $row;
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Dedicated Excel-style Shot Breakdown & Bulk Task Matrix view.
     */
    public function breakdown($projectId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);

        if (!$project) {
            return redirect()->to('/admin/projects')->with('error', 'Project not found.');
        }

        $sequenceModel = new \App\Models\SequenceModel();
        $shotModel = new \App\Models\ShotModel();
        $taskTypeModel = new \App\Models\TaskTypeModel();
        $userModel = new \App\Models\UserModel();

        // Sequences & Shots
        $sequences = $sequenceModel->where('project_id', $projectId)->orderBy('name', 'ASC')->findAll();
        $shots = $shotModel->where('project_id', $projectId)->orderBy('sequence_id', 'ASC')->orderBy('shot_number', 'ASC')->findAll();

        // Tasks assigned to shots
        $taskBuilder = $db->table('tasks');
        $taskBuilder->select('tasks.*, task_types.name as task_type_name, users.name as assigned_user_name, users.experience_level');
        $taskBuilder->join('task_types', 'task_types.id = tasks.task_type_id', 'left');
        $taskBuilder->join('users', 'users.id = tasks.assigned_to', 'left');
        $taskBuilder->where('tasks.project_id', $projectId);
        $taskBuilder->where('tasks.shot_id IS NOT NULL');
        $taskBuilder->orderBy('tasks.id', 'ASC');
        $allTasks = $taskBuilder->get()->getResult();

        // Shot Task Types & Users
        $taskTypes = $taskTypeModel->where('category', 'shot')->findAll();
        $users = $userModel->orderBy('name', 'ASC')->findAll();

        // Load Studio Rate Settings
        $settingsModel = new \App\Models\SettingsModel();
        $studioCurrency = $settingsModel->getSetting('studio_currency', '₹');
        $opsHourlyRate = (float)$settingsModel->getSetting('studio_ops_hourly_rate', 100.00);
        $commissionPct = (float)$settingsModel->getSetting('studio_commission_pct', 30.0);
        $defaultArtistRate = (float)$settingsModel->getSetting('default_artist_rate', 500.00);

        // Map users by ID for quick rate lookup
        $userRateMap = [];
        foreach ($users as $u) {
            $userRateMap[$u->id] = (float)($u->hourly_rate ?? $defaultArtistRate);
        }

        $tasksByShot = [];
        $totalProjectHours = 0.0;
        $totalArtistCost   = 0.0;
        $totalOpsCost      = 0.0;
        $totalClientBudget = 0.0;
        $shotTotalBudgets  = [];
        $taskModel = new \App\Models\TaskModel();

        foreach ($allTasks as $t) {
            if ($t->estimated_hours === null || (float)$t->estimated_hours <= 0) {
                // Auto-calculate on load so it never shows 0h
                $taskModel->update($t->id, [
                    'complexity' => $t->complexity ?: 'Medium',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $refreshed = $taskModel->find($t->id);
                if ($refreshed && $refreshed->estimated_hours) {
                    $t->estimated_hours = $refreshed->estimated_hours;
                }
            }

            $hrs = (float)($t->estimated_hours ?? 0);
            $rate = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
            
            $artistCost = $hrs * $rate;
            $opsCost = $hrs * $opsHourlyRate;
            $margin = ($artistCost + $opsCost) * ($commissionPct / 100.0);
            $clientCost = $artistCost + $opsCost + $margin;

            $t->artist_rate  = $rate;
            $t->artist_cost  = $artistCost;
            $t->ops_cost     = $opsCost;
            $t->client_cost  = $clientCost;

            $totalArtistCost   += $artistCost;
            $totalOpsCost      += $opsCost;
            $totalClientBudget += $clientCost;
            $shotTotalBudgets[$t->shot_id] = ($shotTotalBudgets[$t->shot_id] ?? 0.0) + $clientCost;

            $tasksByShot[$t->shot_id][] = $t;
            $totalProjectHours += $hrs;
        }

        // Proportional Locked Budget Calculations (e.g. ₹40,000 allocated cap)
        $agreedBudget = !empty($project->agreed_budget) ? (float)$project->agreed_budget : 0.0;
        $scaleFactor  = ($agreedBudget > 0 && $totalClientBudget > 0) ? ($agreedBudget / $totalClientBudget) : 1.0;

        $shotAllocatedBudgets = [];
        foreach ($shotTotalBudgets as $sId => $idealCost) {
            $shotAllocatedBudgets[$sId] = $idealCost * $scaleFactor;
        }

        // Apply scale factor to tasks
        foreach ($allTasks as $t) {
            $t->allocated_client_cost = $t->client_cost * $scaleFactor;
            $t->allocated_artist_cost = $t->artist_cost * $scaleFactor;
            $t->allocated_ops_cost    = $t->ops_cost * $scaleFactor;
        }

        // Benchmarks
        $bmRaw = $db->table('task_benchmarks')->where('project_id', $projectId)->get()->getResult();
        $benchmarks = [];
        foreach ($bmRaw as $bm) {
            $benchmarks[$bm->task_type_id] = $bm;
        }

        $data = [
            'pageTitle'             => 'Shot Breakdown: ' . $project->name,
            'project'               => $project,
            'sequences'             => $sequences,
            'shots'                 => $shots,
            'tasksByShot'           => $tasksByShot,
            'taskTypes'             => $taskTypes,
            'users'                 => $users,
            'benchmarks'            => $benchmarks,
            'totalProjectHours'     => $totalProjectHours,
            'totalArtistCost'       => $totalArtistCost,
            'totalOpsCost'          => $totalOpsCost,
            'totalProfitMargin'     => $totalClientBudget - ($totalArtistCost + $totalOpsCost),
            'totalClientBudget'     => $totalClientBudget,
            'agreedBudget'          => $agreedBudget,
            'scaleFactor'           => $scaleFactor,
            'shotTotalBudgets'      => $shotTotalBudgets,
            'shotAllocatedBudgets'  => $shotAllocatedBudgets,
            'studioCurrency'        => $studioCurrency,
            'opsHourlyRate'         => $opsHourlyRate,
            'commissionPct'         => $commissionPct,
            'defaultArtistRate'     => $defaultArtistRate,
        ];

        return view('projects/breakdown', $data);
    }

    /**
     * Update project agreed / locked budget and return live scaled budget allocations.
     */
    public function updateAgreedBudget()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $projectId = (int)$this->request->getPost('project_id');
        $rawBudget = $this->request->getPost('agreed_budget');

        $agreedBudget = ($rawBudget !== '' && $rawBudget !== null) ? (float)$rawBudget : null;

        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);
        if (!$project) {
            return $this->response->setJSON(['success' => false, 'message' => 'Project not found.']);
        }

        $projectModel->update($projectId, ['agreed_budget' => $agreedBudget]);

        // Recalculate all shot and project totals
        $db = \Config\Database::connect();
        $settingsModel = new \App\Models\SettingsModel();
        $studioCurrency = $settingsModel->getSetting('studio_currency', '₹');
        $opsHourlyRate = (float)$settingsModel->getSetting('studio_ops_hourly_rate', 100.00);
        $commissionPct = (float)$settingsModel->getSetting('studio_commission_pct', 30.0);
        $defaultArtistRate = (float)$settingsModel->getSetting('default_artist_rate', 500.00);

        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();
        $userRateMap = [];
        foreach ($users as $u) {
            $userRateMap[$u->id] = (float)($u->hourly_rate ?? $defaultArtistRate);
        }

        $taskModel = new \App\Models\TaskModel();
        $allTasks = $taskModel->where('project_id', $projectId)->findAll();
        $totalClientBudget = 0.0;
        $shotTotals = [];

        foreach ($allTasks as $t) {
            $h = (float)($t->estimated_hours ?? 0);
            $r = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
            $b = ($h * $r + $h * $opsHourlyRate) * (1 + ($commissionPct / 100.0));
            $totalClientBudget += $b;
            if (!empty($t->shot_id)) {
                $shotTotals[$t->shot_id] = ($shotTotals[$t->shot_id] ?? 0.0) + $b;
            }
        }

        $scale = ($agreedBudget > 0 && $totalClientBudget > 0) ? ($agreedBudget / $totalClientBudget) : 1.0;

        $shotAllocated = [];
        foreach ($shotTotals as $sId => $ideal) {
            $shotAllocated[$sId] = round($ideal * $scale, 0);
        }

        $taskAllocated = [];
        foreach ($allTasks as $t) {
            $h = (float)($t->estimated_hours ?? 0);
            $r = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
            $b = ($h * $r + $h * $opsHourlyRate) * (1 + ($commissionPct / 100.0));
            $taskAllocated[$t->id] = [
                'ideal'     => round($b, 0),
                'allocated' => round($b * $scale, 0),
            ];
        }

        return $this->response->setJSON([
            'success'             => true,
            'agreed_budget'       => $agreedBudget ? round($agreedBudget, 0) : null,
            'total_ideal_budget'  => round($totalClientBudget, 0),
            'scale_factor'        => $scale,
            'scale_percent'       => round($scale * 100, 1),
            'shot_totals'         => $shotTotals,
            'shot_allocated'      => $shotAllocated,
            'task_allocated'      => $taskAllocated,
            'currency'            => $studioCurrency,
        ]);
    }

    /**
     * Bulk assign tasks across multiple shots in one click.
     */
    public function bulkAssignTasks()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $projectId = (int)$this->request->getPost('project_id');
        $shotIds = $this->request->getPost('shot_ids');
        $taskTypeId = (int)$this->request->getPost('task_type_id');
        $assignedTo = $this->request->getPost('assigned_to');
        $complexity = $this->request->getPost('complexity') ?: 'Medium';
        $status = $this->request->getPost('status') ?: 'pending';

        if (empty($projectId) || empty($shotIds) || empty($taskTypeId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required fields.']);
        }

        if (!is_array($shotIds)) {
            $shotIds = explode(',', $shotIds);
        }

        $taskModel = new \App\Models\TaskModel();
        $shotModel = new \App\Models\ShotModel();
        $db = \Config\Database::connect();

        $created = 0;
        $updated = 0;

        foreach ($shotIds as $shotId) {
            $shotId = (int)trim($shotId);
            if (empty($shotId)) continue;

            $shot = $shotModel->find($shotId);
            if (!$shot) continue;

            // Check if this task type already exists for this shot
            $existing = $taskModel->where('shot_id', $shotId)->where('task_type_id', $taskTypeId)->first();

            $taskData = [
                'project_id'   => $projectId,
                'shot_id'      => $shotId,
                'task_type_id' => $taskTypeId,
                'assigned_to'  => !empty($assignedTo) ? (int)$assignedTo : null,
                'complexity'   => $complexity,
                'status'       => $status,
                'fps'          => $shot->fps,
                'frame_count'  => $shot->frame_count,
            ];

            if ($existing) {
                $taskModel->update($existing->id, $taskData);
                $updated++;
            } else {
                $taskModel->insert($taskData);
                $created++;
            }

            \App\Libraries\FolderManager::createTaskFolders($projectId, $taskTypeId, $shotId, null);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "Bulk task assignment complete: {$created} created, {$updated} updated."
            ]);
        }

        return redirect()->back()->with('message', "Bulk task assignment complete: {$created} created, {$updated} updated.");
    }

    /**
     * Inline AJAX Task updates (Assignee, Complexity, Status).
     */
    public function inlineUpdateTask()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $taskId = (int)$this->request->getPost('task_id');
        $field  = $this->request->getPost('field');
        $value  = $this->request->getPost('value');

        if (empty($taskId) || empty($field)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters.']);
        }

        $allowed = ['assigned_to', 'complexity', 'status', 'notes', 'fps', 'frame_count'];
        if (!in_array($field, $allowed)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Field not allowed.']);
        }

        $taskModel = new \App\Models\TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task) {
            return $this->response->setJSON(['success' => false, 'message' => 'Task not found.']);
        }

        $updateData = [
            $field => ($value === '' || $value === null) ? null : ($field === 'assigned_to' || $field === 'fps' || $field === 'frame_count' ? (int)$value : $value)
        ];

        $taskModel->update($taskId, $updateData);

        // Fetch refreshed task
        $refreshed = $taskModel->find($taskId);

        // Load Rate Settings
        $settingsModel = new \App\Models\SettingsModel();
        $studioCurrency = $settingsModel->getSetting('studio_currency', '₹');
        $opsHourlyRate = (float)$settingsModel->getSetting('studio_ops_hourly_rate', 100.00);
        $commissionPct = (float)$settingsModel->getSetting('studio_commission_pct', 30.0);
        $defaultArtistRate = (float)$settingsModel->getSetting('default_artist_rate', 500.00);

        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();
        $userRateMap = [];
        foreach ($users as $u) {
            $userRateMap[$u->id] = (float)($u->hourly_rate ?? $defaultArtistRate);
        }

        // Calculate shot totals
        $shotTasks = $taskModel->where('shot_id', $task->shot_id)->findAll();
        $shotTotalHours = 0.0;
        $shotTotalBudget = 0.0;
        foreach ($shotTasks as $st) {
            $h = (float)($st->estimated_hours ?? 0);
            $r = !empty($st->assigned_to) && isset($userRateMap[$st->assigned_to]) ? $userRateMap[$st->assigned_to] : $defaultArtistRate;
            $b = ($h * $r + $h * $opsHourlyRate) * (1 + ($commissionPct / 100.0));
            $shotTotalHours += $h;
            $shotTotalBudget += $b;
        }

        // Refreshed task budget
        $refHrs = (float)($refreshed->estimated_hours ?? 0);
        $refRate = !empty($refreshed->assigned_to) && isset($userRateMap[$refreshed->assigned_to]) ? $userRateMap[$refreshed->assigned_to] : $defaultArtistRate;
        $refTaskBudget = ($refHrs * $refRate + $refHrs * $opsHourlyRate) * (1 + ($commissionPct / 100.0));

        // Calculate project totals
        $projTasks = $taskModel->where('project_id', $task->project_id)->findAll();
        $projTotalHours = 0.0;
        $projTotalBudget = 0.0;
        foreach ($projTasks as $pt) {
            $h = (float)($pt->estimated_hours ?? 0);
            $r = !empty($pt->assigned_to) && isset($userRateMap[$pt->assigned_to]) ? $userRateMap[$pt->assigned_to] : $defaultArtistRate;
            $b = ($h * $r + $h * $opsHourlyRate) * (1 + ($commissionPct / 100.0));
            $projTotalHours += $h;
            $projTotalBudget += $b;
        }

        return $this->response->setJSON([
            'success'           => true,
            'task_id'           => $taskId,
            'estimated_hours'   => $refreshed->estimated_hours ? round($refreshed->estimated_hours, 1) : 0,
            'task_budget'       => round($refTaskBudget, 0),
            'shot_id'           => $task->shot_id,
            'shot_total_hours'  => round($shotTotalHours, 1),
            'shot_total_budget' => round($shotTotalBudget, 0),
            'proj_total_hours'  => round($projTotalHours, 1),
            'proj_total_budget' => round($projTotalBudget, 0),
            'currency'          => $studioCurrency,
        ]);
    }

    /**
     * Inline AJAX Shot updates (frame_count, fps, frame_in, frame_out, comp_name, description).
     */
    public function inlineUpdateShot()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $shotId = (int)$this->request->getPost('shot_id');
        $field  = $this->request->getPost('field');
        $value  = $this->request->getPost('value');

        if (empty($shotId) || empty($field)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters.']);
        }

        $allowed = ['shot_number', 'sequence_id', 'frame_count', 'fps', 'frame_in', 'frame_out', 'comp_name', 'timecode_in', 'timecode_out', 'width', 'height', 'description'];
        if (!in_array($field, $allowed)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Field not allowed.']);
        }

        $shotModel = new \App\Models\ShotModel();
        $shot = $shotModel->find($shotId);
        if (!$shot) {
            return $this->response->setJSON(['success' => false, 'message' => 'Shot not found.']);
        }

        $intFields = ['sequence_id', 'frame_count', 'fps', 'frame_in', 'frame_out', 'width', 'height'];
        $valToSave = ($value === '' || $value === null) ? null : (in_array($field, $intFields) ? (int)$value : $value);

        $shotModel->update($shotId, [$field => $valToSave]);

        // If frame_count or fps was updated, recalculate all tasks under this shot
        $updatedTasks = [];
        $shotTotalHours = 0.0;
        if ($field === 'frame_count' || $field === 'fps') {
            $taskModel = new \App\Models\TaskModel();
            $tasks = $taskModel->where('shot_id', $shotId)->findAll();
            foreach ($tasks as $t) {
                $taskModel->update($t->id, ['updated_at' => date('Y-m-d H:i:s')]);
                $recalc = $taskModel->find($t->id);
                $updatedTasks[] = [
                    'id'    => $recalc->id,
                    'hours' => $recalc->estimated_hours ? round($recalc->estimated_hours, 1) : 0
                ];
                $shotTotalHours += (float)($recalc->estimated_hours ?? 0);
            }
        }

        return $this->response->setJSON([
            'success'          => true,
            'shot_id'          => $shotId,
            'updated_tasks'    => $updatedTasks,
            'shot_total_hours' => round($shotTotalHours, 1),
        ]);
    }

    /**
     * Inline AJAX Add Single Task to a Shot.
     */
    public function inlineAddTask()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $projectId  = (int)$this->request->getPost('project_id');
        $shotId     = (int)$this->request->getPost('shot_id');
        $taskTypeId = (int)$this->request->getPost('task_type_id');
        $assignedTo = $this->request->getPost('assigned_to');
        $complexity = $this->request->getPost('complexity') ?: 'Medium';

        if (empty($projectId) || empty($shotId) || empty($taskTypeId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing parameters.']);
        }

        $shotModel = new \App\Models\ShotModel();
        $shot = $shotModel->find($shotId);
        if (!$shot) {
            return $this->response->setJSON(['success' => false, 'message' => 'Shot not found.']);
        }

        $taskModel = new \App\Models\TaskModel();
        $newId = $taskModel->insert([
            'project_id'   => $projectId,
            'shot_id'      => $shotId,
            'task_type_id' => $taskTypeId,
            'assigned_to'  => !empty($assignedTo) ? (int)$assignedTo : null,
            'complexity'   => $complexity,
            'status'       => 'pending',
            'fps'          => $shot->fps,
            'frame_count'  => $shot->frame_count,
        ]);

        \App\Libraries\FolderManager::createTaskFolders($projectId, $taskTypeId, $shotId, null);

        $db = \Config\Database::connect();
        $task = $db->table('tasks t')
            ->select('t.*, tt.name as task_type_name, u.name as assigned_user_name, u.experience_level')
            ->join('task_types tt', 'tt.id = t.task_type_id', 'left')
            ->join('users u', 'u.id = t.assigned_to', 'left')
            ->where('t.id', $newId)
            ->get()->getRow();

        // Calculate shot total
        $shotTasks = $taskModel->where('shot_id', $shotId)->findAll();
        $shotTotalHours = 0.0;
        foreach ($shotTasks as $st) {
            $shotTotalHours += (float)($st->estimated_hours ?? 0);
        }

        return $this->response->setJSON([
            'success'          => true,
            'task'             => $task,
            'shot_id'          => $shotId,
            'shot_total_hours' => round($shotTotalHours, 1),
        ]);
    }

    /**
     * Inline AJAX Delete Task.
     */
    public function deleteTaskAjax()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $taskId = (int)$this->request->getPost('task_id');
        if (empty($taskId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid task ID.']);
        }

        $taskModel = new \App\Models\TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task) {
            return $this->response->setJSON(['success' => false, 'message' => 'Task not found.']);
        }

        $shotId = $task->shot_id;
        $projectId = $task->project_id;
        $taskModel->delete($taskId);

        $shotTotalHours = 0.0;
        if ($shotId) {
            $shotTasks = $taskModel->where('shot_id', $shotId)->findAll();
            foreach ($shotTasks as $st) {
                $shotTotalHours += (float)($st->estimated_hours ?? 0);
            }
        }

        return $this->response->setJSON([
            'success'          => true,
            'task_id'          => $taskId,
            'shot_id'          => $shotId,
            'shot_total_hours' => round($shotTotalHours, 1),
        ]);
    }

    /**
     * Dedicated Project Production, Financial & Risk Analysis Intelligence Page.
     */
    public function analysis($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($id);
        if (!$project) {
            return redirect()->to('/admin/projects')->with('error', 'Project not found.');
        }

        $db = \Config\Database::connect();

        // Client & Project Type
        $client = $db->table('clients')->where('id', $project->client_id)->get()->getRow();
        $projectType = $db->table('project_types')->where('id', $project->project_type_id)->get()->getRow();
        $project->client_name = $client->company_name ?? 'Unknown Client';
        $project->project_type_name = $projectType->name ?? 'Standard Project';

        // Settings & Rate Lookups
        $settingsModel = new \App\Models\SettingsModel();
        $studioCurrency = $settingsModel->getSetting('studio_currency', '₹');
        $opsHourlyRate = (float)$settingsModel->getSetting('studio_ops_hourly_rate', 100.00);
        $commissionPct = (float)$settingsModel->getSetting('studio_commission_pct', 30.0);
        $defaultArtistRate = (float)$settingsModel->getSetting('default_artist_rate', 500.00);

        $userModel = new \App\Models\UserModel();
        $allUsers = $userModel->findAll();
        $userMap = [];
        $userRateMap = [];
        foreach ($allUsers as $u) {
            $userMap[$u->id] = $u;
            $userRateMap[$u->id] = (float)($u->hourly_rate ?? $defaultArtistRate);
        }

        // Sequences & Shots
        $sequences = $db->table('sequences')->where('project_id', $id)->get()->getResult();
        $shots = $db->table('shots')->where('project_id', $id)->get()->getResult();

        // Tasks
        $taskBuilder = $db->table('tasks');
        $taskBuilder->select('tasks.*, task_types.name as task_type_name, users.name as assigned_user_name, users.global_role as user_role, users.experience_level');
        $taskBuilder->join('task_types', 'task_types.id = tasks.task_type_id', 'left');
        $taskBuilder->join('users', 'users.id = tasks.assigned_to', 'left');
        $taskBuilder->where('tasks.project_id', $id);
        $tasks = $taskBuilder->get()->getResult();

        // 1. TIMELINE & CALENDAR ANALYSIS
        $now = new \DateTime();
        $startDt = !empty($project->start_date) ? new \DateTime($project->start_date) : new \DateTime($project->created_at);
        $deadlineDt = !empty($project->deadline) ? new \DateTime($project->deadline) : (clone $startDt)->modify('+30 days');

        $totalDurationDays = max(1, (int)$startDt->diff($deadlineDt)->format('%r%a'));
        $daysElapsed = max(0, (int)$startDt->diff($now)->format('%r%a'));
        $daysRemaining = (int)$now->diff($deadlineDt)->format('%r%a');
        $isOverdue = $daysRemaining < 0;

        // Calculate working days remaining (excluding Sat/Sun)
        $workingDaysRemaining = 0;
        if ($daysRemaining > 0) {
            $cursor = clone $now;
            while ($cursor <= $deadlineDt) {
                $w = (int)$cursor->format('N'); // 1 = Mon, 7 = Sun
                if ($w < 6) {
                    $workingDaysRemaining++;
                }
                $cursor->modify('+1 day');
            }
        }
        $workingDaysRemaining = max(1, $workingDaysRemaining);

        // 2. PRODUCTION WORKLOAD & VELOCITY
        $totalEstHours = 0.0;
        $completedHours = 0.0;
        $inProgressHours = 0.0;
        $pendingHours = 0.0;
        $unassignedTaskCount = 0;
        $unassignedHours = 0.0;

        $rawArtistCost = 0.0;
        $rawOpsCost = 0.0;
        $idealClientBudget = 0.0;

        $artistWorkloadMap = []; // Artist ID => [name, role, tasks_count, total_hours, total_cost]
        $complexShotsCount = 0;

        foreach ($tasks as $t) {
            $h = (float)($t->estimated_hours ?? 0);
            $r = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
            
            $artCost = $h * $r;
            $opsCost = $h * $opsHourlyRate;
            $clientPrice = ($artCost + $opsCost) * (1 + ($commissionPct / 100.0));

            $totalEstHours += $h;
            $rawArtistCost += $artCost;
            $rawOpsCost += $opsCost;
            $idealClientBudget += $clientPrice;

            if (in_array($t->status, ['completed', 'approved'])) {
                $completedHours += $h;
            } elseif ($t->status === 'in_progress') {
                $inProgressHours += $h;
            } else {
                $pendingHours += $h;
            }

            if (empty($t->assigned_to)) {
                $unassignedTaskCount++;
                $unassignedHours += $h;
            } else {
                $uId = $t->assigned_to;
                if (!isset($artistWorkloadMap[$uId])) {
                    $artistWorkloadMap[$uId] = [
                        'id'          => $uId,
                        'name'        => $t->assigned_user_name ?? 'Artist #' . $uId,
                        'role'        => $t->user_role ?? 'artist',
                        'tasks_count' => 0,
                        'total_hours' => 0.0,
                        'artist_cost' => 0.0,
                    ];
                }
                $artistWorkloadMap[$uId]['tasks_count']++;
                $artistWorkloadMap[$uId]['total_hours'] += $h;
                $artistWorkloadMap[$uId]['artist_cost'] += $artCost;
            }

            if (in_array($t->complexity, ['High', 'Extreme', 'Complex'])) {
                $complexShotsCount++;
            }
        }

        $remainingHours = max(0.0, $totalEstHours - $completedHours);
        $completionPct = $totalEstHours > 0 ? round(($completedHours / $totalEstHours) * 100, 1) : 0.0;

        // Daily Velocity required
        $requiredDailyHours = $workingDaysRemaining > 0 ? round($remainingHours / $workingDaysRemaining, 1) : $remainingHours;

        // 3. ARTIST CAPACITY & STAFFING (STRICTLY PRODUCTION ARTISTS)
        $productionArtistsOnly = array_filter($artistWorkloadMap, fn($a) => ($a['role'] ?? '') === 'artist' || empty($a['role']));
        $activeArtistsCount = count($productionArtistsOnly);

        // Also check all artists in studio if no one assigned yet
        $studioArtists = array_filter($allUsers, fn($u) => ($u->global_role ?? '') === 'artist');
        $totalStudioArtistsCount = count($studioArtists);

        // Effective artists working on this project (fallback to studio artists count if unassigned)
        $effectiveArtistsCount = max(1, $activeArtistsCount > 0 ? $activeArtistsCount : $totalStudioArtistsCount);
        $dailyTeamCapacity = $effectiveArtistsCount * 8.0; // 8 hours per artist per day
        $totalDeliverableHours = $dailyTeamCapacity * $workingDaysRemaining;
        $hoursDeficitOrSurplus = $totalDeliverableHours - $remainingHours;

        $recommendedArtistsNeeded = max(1, (int)ceil($requiredDailyHours / 8.0));
        $additionalArtistsNeeded = max(0, $recommendedArtistsNeeded - $effectiveArtistsCount);

        // 4. FINANCIAL HEALTH & PROFIT/LOSS ENGINE
        $rawTotalCost = $rawArtistCost + $rawOpsCost;
        $agreedBudget = (float)($project->agreed_budget ?? 0);
        $effectiveRevenue = $agreedBudget > 0 ? $agreedBudget : $idealClientBudget;
        $projectedNetProfit = $effectiveRevenue - $rawTotalCost;
        $projectedMarginPct = $effectiveRevenue > 0 ? round(($projectedNetProfit / $effectiveRevenue) * 100, 1) : 0.0;
        
        $isLoss = $projectedNetProfit < 0;
        $lossAmount = $isLoss ? abs($projectedNetProfit) : 0.0;
        $scaleFactor = ($agreedBudget > 0 && $idealClientBudget > 0) ? ($agreedBudget / $idealClientBudget) : 1.0;

        // Burn Rate (Daily Cost)
        $dailyBurnRate = $workingDaysRemaining > 0 ? round($rawTotalCost / max(1, $totalDurationDays), 0) : 0;

        // 5. RISK & ISSUE INTELLIGENCE MATRIX
        $risks = [];

        // Risk A: Budget Deficit / Loss
        if ($isLoss) {
            $risks[] = [
                'level'       => 'CRITICAL',
                'title'       => 'Financial Loss Deficit (' . $studioCurrency . number_format($lossAmount, 0) . ' Loss)',
                'description' => 'The client agreed budget of ' . $studioCurrency . number_format($agreedBudget, 0) . ' does not cover raw production costs (' . $studioCurrency . number_format($rawTotalCost, 0) . '). Studio will lose money on direct payouts unless scope or artist rates are renegotiated.',
                'action'      => 'Negotiate budget uplift to minimum ' . $studioCurrency . number_format($rawTotalCost, 0) . ' (break-even) or compress artist freelancer rates.',
            ];
        } elseif ($projectedMarginPct < 15.0 && $agreedBudget > 0) {
            $risks[] = [
                'level'       => 'HIGH',
                'title'       => 'Severe Margin Compression (' . $projectedMarginPct . '% margin)',
                'description' => 'Current profit margin (' . $projectedMarginPct . '%) is dangerously below the studio benchmark standard of ' . $commissionPct . '%. Minor delays or revisions will push the project into net loss.',
                'action'      => 'Cap revision rounds in client contract and monitor artist delivery hours strictly.',
            ];
        }

        // Risk B: Staffing Deficit & Delivery Delay
        if ($hoursDeficitOrSurplus < 0) {
            $risks[] = [
                'level'       => 'HIGH',
                'title'       => 'Staffing Deficit: Need +' . $additionalArtistsNeeded . ' Artists',
                'description' => 'Required workload (' . round($remainingHours, 1) . ' hrs) exceeds total team capacity (' . round($totalDeliverableHours, 1) . ' hrs) across the remaining ' . $workingDaysRemaining . ' working days.',
                'action'      => 'Assign at least ' . $recommendedArtistsNeeded . ' dedicated artists immediately or extend delivery deadline.',
            ];
        }

        // Risk C: Unassigned Tasks
        if ($unassignedTaskCount > 0) {
            $risks[] = [
                'level'       => $unassignedTaskCount > 10 ? 'HIGH' : 'MEDIUM',
                'title'       => $unassignedTaskCount . ' Unassigned Tasks (' . round($unassignedHours, 1) . ' hrs unallocated)',
                'description' => 'Shots have pending tasks with no artist assigned. Unallocated tasks cause invisible pipeline stalls and sudden deadline compression.',
                'action'      => 'Open Shot Breakdown Matrix and bulk assign artists to all unassigned tasks.',
            ];
        }

        // Risk D: Imminent Deadline / Overdue
        if ($isOverdue) {
            $risks[] = [
                'level'       => 'CRITICAL',
                'title'       => 'Project is OVERDUE by ' . abs($daysRemaining) . ' Days',
                'description' => 'The agreed deadline (' . $deadlineDt->format('M d, Y') . ') has passed with ' . round($remainingHours, 1) . ' hrs of work remaining.',
                'action'      => 'Re-align delivery schedule with client immediately and execute emergency rush assignments.',
            ];
        } elseif ($daysRemaining <= 5 && $completionPct < 70.0) {
            $risks[] = [
                'level'       => 'HIGH',
                'title'       => 'Deadline Imminent (Only ' . $daysRemaining . ' days left, ' . $completionPct . '% complete)',
                'description' => 'Project completion is below 70% with less than 5 days before scheduled delivery.',
                'action'      => 'Prioritize hero shots and schedule artist overtime.',
            ];
        }

        // Risk E: High Complexity Concentration
        if ($complexShotsCount > 0 && count($tasks) > 0 && ($complexShotsCount / count($tasks)) > 0.3) {
            $risks[] = [
                'level'       => 'MEDIUM',
                'title'       => 'High Complexity Density (' . $complexShotsCount . ' complex tasks)',
                'description' => 'Over 30% of project tasks are tagged High/Extreme complexity, increasing potential revision cycles.',
                'action'      => 'Ensure senior lead artists supervise initial comp setups.',
            ];
        }

        $data = [
            'pageTitle'                 => 'Production & Risk Analysis: ' . $project->name,
            'project'                   => $project,
            'sequences'                 => $sequences,
            'shots'                     => $shots,
            'tasks'                     => $tasks,
            'studioCurrency'            => $studioCurrency,
            'opsHourlyRate'             => $opsHourlyRate,
            'commissionPct'             => $commissionPct,
            
            // Timeline
            'startDt'                   => $startDt,
            'deadlineDt'                => $deadlineDt,
            'totalDurationDays'         => $totalDurationDays,
            'daysElapsed'               => $daysElapsed,
            'daysRemaining'             => $daysRemaining,
            'workingDaysRemaining'      => $workingDaysRemaining,
            'isOverdue'                 => $isOverdue,
            
            // Workload & Velocity
            'totalEstHours'             => round($totalEstHours, 1),
            'completedHours'            => round($completedHours, 1),
            'inProgressHours'           => round($inProgressHours, 1),
            'pendingHours'              => round($pendingHours, 1),
            'remainingHours'            => round($remainingHours, 1),
            'completionPct'             => $completionPct,
            'requiredDailyHours'        => $requiredDailyHours,
            
            // Staffing
            'activeArtistsCount'        => $activeArtistsCount,
            'effectiveArtistsCount'     => $effectiveArtistsCount,
            'dailyTeamCapacity'         => $dailyTeamCapacity,
            'totalDeliverableHours'     => round($totalDeliverableHours, 1),
            'hoursDeficitOrSurplus'     => round($hoursDeficitOrSurplus, 1),
            'recommendedArtistsNeeded'  => $recommendedArtistsNeeded,
            'additionalArtistsNeeded'   => $additionalArtistsNeeded,
            'artistWorkloadMap'         => $artistWorkloadMap,
            'unassignedTaskCount'       => $unassignedTaskCount,
            'unassignedHours'           => round($unassignedHours, 1),
            
            // Financials & P&L
            'rawArtistCost'             => round($rawArtistCost, 0),
            'rawOpsCost'                => round($rawOpsCost, 0),
            'rawTotalCost'              => round($rawTotalCost, 0),
            'idealClientBudget'         => round($idealClientBudget, 0),
            'agreedBudget'              => round($agreedBudget, 0),
            'effectiveRevenue'          => round($effectiveRevenue, 0),
            'projectedNetProfit'        => round($projectedNetProfit, 0),
            'projectedMarginPct'        => $projectedMarginPct,
            'isLoss'                    => $isLoss,
            'lossAmount'                => round($lossAmount, 0),
            'scaleFactor'               => $scaleFactor,
            'dailyBurnRate'             => $dailyBurnRate,
            
            // Risks
            'risks'                     => $risks,
        ];

        return view('projects/analysis', $data);
    }

    /**
     * AJAX: Assign or Update Project Supervisor
     */
    public function updateSupervisor($id)
    {
        try {
            if (!has_any_role(['site_manager', 'admin', 'project_manager']) && !is_project_supervisor($id)) {
                return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(403);
            }

            $supervisorId = $this->request->getPost('supervisor_id');
            $supervisorId = (!empty($supervisorId) && is_numeric($supervisorId)) ? (int)$supervisorId : null;

            $db = \Config\Database::connect();
            $projectsTable = $db->prefixTable('projects');
            
            // 1. Ensure supervisor_id column exists on prefixed projects table
            try {
                $check = $db->query("SHOW COLUMNS FROM `{$projectsTable}` LIKE 'supervisor_id'")->getRow();
                if (!$check) {
                    $db->query("ALTER TABLE `{$projectsTable}` ADD COLUMN `supervisor_id` INT UNSIGNED NULL");
                }
            } catch (\Throwable $colEx) {
                log_message('error', 'Auto-add supervisor_id failed: ' . $colEx->getMessage());
            }

            // 2. Perform direct update
            $db->table('projects')->where('id', (int)$id)->update(['supervisor_id' => $supervisorId]);

            // 3. Automatically append project_manager role to the user
            $userName = 'Unassigned';
            if ($supervisorId) {
                $userRow = $db->table('users')->where('id', $supervisorId)->get()->getRow();
                if ($userRow) {
                    $userName = $userRow->name;
                    $currentRoles = !empty($userRow->roles) ? json_decode($userRow->roles, true) : [$userRow->global_role ?? 'artist'];
                    if (!is_array($currentRoles)) $currentRoles = [$userRow->global_role ?? 'artist'];
                    if (!in_array('project_manager', $currentRoles)) {
                        $currentRoles[] = 'project_manager';
                        $db->table('users')->where('id', $supervisorId)->update([
                            'roles' => json_encode(array_values(array_unique($currentRoles)))
                        ]);
                    }
                }
            }

            return $this->response->setJSON([
                'success'         => true,
                'message'         => 'Project Supervisor updated successfully',
                'supervisor_name' => $userName,
                'supervisor_id'   => $supervisorId
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'updateSupervisor error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error'   => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * AJAX: Assign or Update Sequence Supervisor
     */
    public function updateSequenceSupervisor($id)
    {
        try {
            if (!has_any_role(['site_manager', 'admin', 'project_manager']) && !is_sequence_supervisor($id)) {
                return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(403);
            }

            $supervisorId = $this->request->getPost('supervisor_id');
            $supervisorId = (!empty($supervisorId) && is_numeric($supervisorId)) ? (int)$supervisorId : null;

            $db = \Config\Database::connect();
            $sequencesTable = $db->prefixTable('sequences');
            
            // 1. Ensure supervisor_id column exists on prefixed sequences table
            try {
                $check = $db->query("SHOW COLUMNS FROM `{$sequencesTable}` LIKE 'supervisor_id'")->getRow();
                if (!$check) {
                    $db->query("ALTER TABLE `{$sequencesTable}` ADD COLUMN `supervisor_id` INT UNSIGNED NULL");
                }
            } catch (\Throwable $colEx) {
                log_message('error', 'Auto-add sequence supervisor_id failed: ' . $colEx->getMessage());
            }

            // 2. Perform direct update
            $db->table('sequences')->where('id', (int)$id)->update(['supervisor_id' => $supervisorId]);

            // 3. Automatically append project_manager role to the user
            $userName = 'Unassigned';
            if ($supervisorId) {
                $userRow = $db->table('users')->where('id', $supervisorId)->get()->getRow();
                if ($userRow) {
                    $userName = $userRow->name;
                    $currentRoles = !empty($userRow->roles) ? json_decode($userRow->roles, true) : [$userRow->global_role ?? 'artist'];
                    if (!is_array($currentRoles)) $currentRoles = [$userRow->global_role ?? 'artist'];
                    if (!in_array('project_manager', $currentRoles)) {
                        $currentRoles[] = 'project_manager';
                        $db->table('users')->where('id', $supervisorId)->update([
                            'roles' => json_encode(array_values(array_unique($currentRoles)))
                        ]);
                    }
                }
            }

            return $this->response->setJSON([
                'success'         => true,
                'message'         => 'Sequence Supervisor updated successfully',
                'supervisor_name' => $userName,
                'supervisor_id'   => $supervisorId
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'updateSequenceSupervisor error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error'   => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}


