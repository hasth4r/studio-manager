<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\SchedulerEngine;

class Scheduling extends BaseController
{
    private function db() { return \Config\Database::connect(); }
    private function requireLogin(): bool
    {
        return (bool)session()->get('isLoggedIn');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DATA API — used by the new Gantt JS to load everything
    // ──────────────────────────────────────────────────────────────────────────

    public function getSchedulingData()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status'=>'error','message'=>'Unauthorized']);

        $db = $this->db();
        $projectIds = $this->request->getGet('project_ids'); // comma-separated or 'all'

        // Projects
        $projectsQ = $db->table('projects')->select('id,name,deadline');
        if ($projectIds && $projectIds !== 'all') {
            $projectsQ->whereIn('id', explode(',', $projectIds));
        }
        $projects = $projectsQ->get()->getResultArray();
        $pIds = array_column($projects, 'id');

        // Artists & Production Team
        $artists = $db->table('users')
            ->select('id,name,global_role,roles,weekly_hours')
            ->where('status !=', 'inactive')
            ->groupStart()
                ->whereIn('global_role', ['artist', 'Internal Artist', 'admin', 'freelancer', 'project_manager'])
                ->orLike('roles', 'artist')
                ->orLike('roles', 'freelancer')
            ->groupEnd()
            ->get()->getResultArray();

        // Phases
        $phases = [];
        if (!empty($pIds)) {
            $phases = $db->table('project_phases')
                ->whereIn('project_id', $pIds)
                ->orderBy('project_id')->orderBy('sort_order')
                ->get()->getResultArray();
        }

        // Tasks with all joins
        $tasks = [];
        if (!empty($pIds)) {
            $tasks = $db->table('tasks')
                ->select('tasks.id, tasks.project_id, tasks.shot_id, tasks.assigned_to, tasks.status,
                          tasks.estimated_hours, tasks.start_date, tasks.end_date,
                          tasks.priority_percentage, tasks.is_undocked, tasks.phase_id,
                          tasks.frame_count, tasks.fps, tasks.complexity, tasks.gantt_row,
                          task_types.name as task_type_name,
                          shots.shot_number, shots.thumbnail_path, sequences.name as sequence_name,
                          users.name as artist_name,
                          project_phases.name as phase_name, project_phases.color as phase_color,
                          projects.name as project_name')
                ->join('task_types',    'task_types.id = tasks.task_type_id',  'left')
                ->join('shots',         'shots.id = tasks.shot_id',         'left')
                ->join('sequences',     'sequences.id = shots.sequence_id',   'left')
                ->join('users',         'users.id = tasks.assigned_to',     'left')
                ->join('project_phases','project_phases.id = tasks.phase_id',       'left')
                ->join('projects',      'projects.id = tasks.project_id',      'left')
                ->whereIn('tasks.project_id', $pIds)
                ->get()->getResultArray();
        }

        // Unscheduled shots (for search panel)
        $shots = [];
        if (!empty($pIds)) {
            $shots = $db->table('shots')
                ->select('shots.id, shots.shot_number, shots.project_id, shots.thumbnail_path, shots.description,
                          projects.name as project_name, sequences.name as sequence_name')
                ->join('projects',   'projects.id = shots.project_id',     'left')
                ->join('sequences',  'sequences.id = shots.sequence_id',  'left')
                ->whereIn('shots.project_id', $pIds)
                ->orderBy('shots.project_id')->orderBy('shots.shot_number')
                ->get()->getResultArray();
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'projects' => $projects,
            'artists'  => $artists,
            'phases'   => $phases,
            'tasks'    => $tasks,
            'shots'    => $shots,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MAIN PAGE
    // ──────────────────────────────────────────────────────────────────────────

    public function index()
    {
        if (!$this->requireLogin()) return redirect()->to('/login');

        $db = $this->db();
        $userId = (int)session()->get('userId');
        
        $projectsQ = $db->table('projects');
        if (!has_any_role(['site_manager', 'admin'])) {
            $supervisedProj = $db->table('projects')->select('id')->where('supervisor_id', $userId)->get()->getResultArray();
            $supervisedSeq = $db->table('sequences')->select('project_id')->where('supervisor_id', $userId)->get()->getResultArray();
            $allowedIds = array_values(array_unique(array_filter(array_merge(
                array_column($supervisedProj, 'id'),
                array_column($supervisedSeq, 'project_id')
            ))));
            if (!empty($allowedIds)) {
                $projectsQ->whereIn('id', $allowedIds);
            } else {
                $projectsQ->where('id', -1);
            }
        }
        $projects  = $projectsQ->get()->getResultArray();
        $projectId = $this->request->getGet('project_id') ?? ($projects[0]['id'] ?? null);

        $tasks    = [];
        $users    = [];
        $project  = null;
        $holidays = [];

        if ($projectId) {
            $project = $db->table('projects')->where('id', $projectId)->get()->getRow();

            $tasks = $db->table('tasks')
                ->select('tasks.*, users.name as assigned_to_name, users.weekly_hours as artist_weekly_hours,
                          task_types.name as task_name, shots.shot_number, shots.frame_count as shot_frames, sequences.name as sequence_name')
                ->join('users',      'users.id = tasks.assigned_to',    'left')
                ->join('task_types', 'task_types.id = tasks.task_type_id',  'left')
                ->join('shots',      'shots.id = tasks.shot_id',        'left')
                ->join('sequences',  'sequences.id = shots.sequence_id',  'left')
                ->where('tasks.project_id', $projectId)
                ->orderBy('tasks.priority_percentage', 'DESC')
                ->get()->getResultArray();

            $users = $db->table('users')
                ->select('id, name, global_role, roles, weekly_hours')
                ->where('status !=', 'inactive')
                ->groupStart()
                    ->whereIn('global_role', ['artist', 'Internal Artist', 'freelancer'])
                    ->orLike('roles', 'artist')
                    ->orLike('roles', 'freelancer')
                ->groupEnd()
                ->get()->getResultArray();

            $holidays = $db->table('holidays')
                ->orderBy('holiday_date', 'ASC')
                ->get()->getResultArray();
        }

        return view('admin/scheduling/index', [
            'title'            => 'AI Scheduler',
            'fullScreen'       => true,
            'projects'         => $projects,
            'currentProjectId' => $projectId,
            'project'          => $project,
            'tasks'            => $tasks,
            'users'            => $users,
            'holidays'         => $holidays,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // AI AUTO-SCHEDULE
    // ──────────────────────────────────────────────────────────────────────────

    public function autoSchedule()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $projectId = $this->request->getPost('project_id');
        $backwards = (bool)$this->request->getPost('backwards');

        if (!$projectId)
            return $this->response->setJSON(['status' => 'error', 'message' => 'No project selected']);

        $engine       = new SchedulerEngine();
        $previewTasks = $engine->autoSchedule((int)$projectId, $backwards);

        return $this->response->setJSON([
            'status'        => 'success',
            'preview_tasks' => $previewTasks,
            'backwards'     => $backwards,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SAVE DATES (after drag or AI preview)
    // ──────────────────────────────────────────────────────────────────────────

    public function saveDates()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $updates = json_decode($this->request->getPost('updates'), true);
        if (empty($updates))
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nothing to save']);

        $db = $this->db();
        helper('notification');

        foreach ($updates as $u) {
            $data = [
                'start_date'  => $u['start_date'],
                'end_date'    => $u['end_date'],
                'is_undocked' => $u['is_undocked'] ?? 0,
            ];
            
            $assignedTo = null;
            if (array_key_exists('assigned_to', $u)) {
                $data['assigned_to'] = $u['assigned_to'] ?: null;
                $assignedTo = $data['assigned_to'];
            }
            if (array_key_exists('gantt_row', $u)) {
                $data['gantt_row'] = (int)$u['gantt_row'];
            }
            
            // Check if assigning to someone new
            $oldTask = $db->table('tasks')->select('assigned_to')->where('id', $u['id'])->get()->getRow();
            
            $db->table('tasks')->where('id', $u['id'])->update($data);
            
            // Send Notification if assigned_to changed
            if ($assignedTo && (!$oldTask || $oldTask->assigned_to != $assignedTo)) {
                $taskData = $db->table('tasks')
                    ->select('task_types.name as task_name, shots.shot_number, assets.name as asset_name, projects.name as project_name')
                    ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
                    ->join('shots', 'shots.id = tasks.shot_id', 'left')
                    ->join('assets', 'assets.id = tasks.asset_id', 'left')
                    ->join('projects', 'projects.id = tasks.project_id', 'left')
                    ->where('tasks.id', $u['id'])
                    ->get()->getRow();
                    
                if ($taskData) {
                    $target = $taskData->shot_number ? "Shot {$taskData->shot_number}" : ($taskData->asset_name ? "Asset {$taskData->asset_name}" : "a task");
                    $msg = "You have been assigned to {$taskData->task_name} for {$target} in {$taskData->project_name}.";
                    send_notification($assignedTo, 'task_assigned', 'Task Assigned', $msg, '/user/dashboard');
                }
            }
        }

        return $this->response->setJSON(['status' => 'success', 'saved' => count($updates)]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PROJECT DEADLINE
    // ──────────────────────────────────────────────────────────────────────────

    public function setDeadline()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $projectId = $this->request->getPost('project_id');
        $deadline  = $this->request->getPost('deadline');

        if (!$projectId || !$deadline)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing fields']);

        $this->db()->table('projects')->where('id', $projectId)->update(['deadline' => $deadline]);

        return $this->response->setJSON(['status' => 'success']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HOLIDAYS
    // ──────────────────────────────────────────────────────────────────────────

    public function saveHoliday()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $date  = $this->request->getPost('holiday_date');
        $type  = $this->request->getPost('holiday_type') ?? 'public';
        $desc  = $this->request->getPost('description') ?? '';

        if (!$date)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Date required']);

        $db = $this->db();

        // Upsert
        $exists = $db->table('holidays')->where('holiday_date', $date)->countAllResults();
        if ($exists) {
            $db->table('holidays')->where('holiday_date', $date)->update([
                'holiday_type' => $type, 'description' => $desc
            ]);
        } else {
            $db->table('holidays')->insert([
                'holiday_date' => $date, 'holiday_type' => $type,
                'description'  => $desc, 'created_at'   => date('Y-m-d H:i:s')
            ]);
        }

        $id = $exists ? $db->table('holidays')->where('holiday_date', $date)->get()->getRow()->id
                      : $db->insertID();

        return $this->response->setJSON([
            'status' => 'success',
            'id'     => $id,
            'date'   => $date,
            'type'   => $type,
            'desc'   => $desc,
        ]);
    }

    public function deleteHoliday()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $id = $this->request->getPost('id');
        if (!$id) return $this->response->setJSON(['status' => 'error', 'message' => 'ID required']);

        $this->db()->table('holidays')->where('id', $id)->delete();
        return $this->response->setJSON(['status' => 'success']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ARTIST CAPACITY
    // ──────────────────────────────────────────────────────────────────────────

    public function updateCapacity()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $userId      = $this->request->getPost('user_id');
        $weeklyHours = (int)$this->request->getPost('weekly_hours');

        if (!$userId || $weeklyHours < 1 || $weeklyHours > 80)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data']);

        $this->db()->table('users')->where('id', $userId)->update(['weekly_hours' => $weeklyHours]);
        return $this->response->setJSON(['status' => 'success']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // UPDATE TASK ESTIMATE (inline)
    // ──────────────────────────────────────────────────────────────────────────

    public function updateEstimate()
    {
        if (!$this->requireLogin())
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $taskId = $this->request->getPost('task_id');
        $hours  = (float)$this->request->getPost('estimated_hours');

        if (!$taskId || $hours < 0)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data']);

        $this->db()->table('tasks')->where('id', $taskId)->update(['estimated_hours' => $hours]);
        return $this->response->setJSON(['status' => 'success']);
    }
}
