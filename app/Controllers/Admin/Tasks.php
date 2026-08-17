<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Tasks extends BaseController
{
    public function store()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rules = [
            'task_type_id' => 'required|is_natural_no_zero',
            'project_id'   => 'required|is_natural_no_zero',
            'assigned_to'  => 'permit_empty|is_natural_no_zero',
            'complexity'   => 'permit_empty|in_list[Simple,Medium,Complex]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Validation failed for Task assignment.');
        }

        $model = new \App\Models\TaskModel();
        
        $shotId = $this->request->getPost('shot_id');
        $assetId = $this->request->getPost('asset_id');
        $assignedTo = $this->request->getPost('assigned_to');
        $complexity = $this->request->getPost('complexity');

        $model->insert([
            'project_id'   => $this->request->getPost('project_id'),
            'task_type_id' => $this->request->getPost('task_type_id'),
            'shot_id'      => empty($shotId) ? null : $shotId,
            'asset_id'     => empty($assetId) ? null : $assetId,
            'assigned_to'  => empty($assignedTo) ? null : $assignedTo,
            'complexity'   => empty($complexity) ? 'Medium' : $complexity,
            'status'       => 'pending',
            'fps'          => $this->request->getPost('fps') ? (int)$this->request->getPost('fps') : null,
            'frame_count'  => $this->request->getPost('frame_count') ? (int)$this->request->getPost('frame_count') : null,
        ]);

        \App\Libraries\FolderManager::createTaskFolders(
            $this->request->getPost('project_id'),
            $this->request->getPost('task_type_id'),
            empty($shotId) ? null : $shotId,
            empty($assetId) ? null : $assetId
        );

        if (!empty($assignedTo)) {
            helper('notification');
            $db = \Config\Database::connect();
            $taskData = $db->table('tasks t')
                ->select('c.name as task_name, s.shot_number, seq.name as sequence_name, s.thumbnail_path as shot_thumb, a.name as asset_name, a.thumbnail_path as asset_thumb, p.name as project_name, t.estimated_hours, t.due_date, t.complexity')
                ->join('task_types c', 'c.id = t.task_type_id', 'left')
                ->join('shots s', 's.id = t.shot_id', 'left')
                ->join('sequences seq', 'seq.id = s.sequence_id', 'left')
                ->join('assets a', 'a.id = t.asset_id', 'left')
                ->join('projects p', 'p.id = t.project_id', 'left')
                ->where('t.id', $model->getInsertID())
                ->get()->getRow();
                
            if ($taskData) {
                $shotText = $taskData->shot_number ? ($taskData->sequence_name ? "{$taskData->sequence_name} / {$taskData->shot_number}" : "Shot {$taskData->shot_number}") : null;
                $target = $shotText ?: ($taskData->asset_name ? "Asset {$taskData->asset_name}" : "General Task");
                $thumb  = $taskData->shot_thumb ?: $taskData->asset_thumb;
                $est    = $taskData->estimated_hours ? "{$taskData->estimated_hours} hrs" : 'Not set';
                $due    = $taskData->due_date ? date('d M Y', strtotime($taskData->due_date)) : 'Not set';
                
                $msg = "*Project:* {$taskData->project_name}\n"
                     . "*Target:* {$target}\n"
                     . "*Task:* {$taskData->task_name}\n"
                     . "*Complexity:* {$taskData->complexity}\n"
                     . "*Est. Time:* {$est}\n"
                     . "*Due Date:* {$due}";
                     
                send_notification($assignedTo, 'task_assigned', 'New Task Assigned', $msg, '/user/dashboard', $thumb);
            }
        }

        return redirect()->back()->with('message', 'Task added successfully.');
    }

    public function updateAssignee()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rules = [
            'task_id'     => 'required|is_natural_no_zero',
            'assigned_to' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Validation failed for assigning task.');
        }

        $model = new \App\Models\TaskModel();
        
        $taskId = $this->request->getPost('task_id');
        $assignedTo = $this->request->getPost('assigned_to');

        // Check if task exists
        $task = $model->find($taskId);
        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        $model->update($taskId, [
            'assigned_to' => empty($assignedTo) ? null : $assignedTo,
        ]);

        if (!empty($assignedTo)) {
            helper('notification');
            $db = \Config\Database::connect();
            $taskData = $db->table('tasks t')
                ->select('c.name as task_name, s.shot_number, seq.name as sequence_name, s.thumbnail_path as shot_thumb, a.name as asset_name, a.thumbnail_path as asset_thumb, p.name as project_name, t.estimated_hours, t.due_date, t.complexity')
                ->join('task_types c', 'c.id = t.task_type_id', 'left')
                ->join('shots s', 's.id = t.shot_id', 'left')
                ->join('sequences seq', 'seq.id = s.sequence_id', 'left')
                ->join('assets a', 'a.id = t.asset_id', 'left')
                ->join('projects p', 'p.id = t.project_id', 'left')
                ->where('t.id', $taskId)
                ->get()->getRow();
                
            if ($taskData) {
                $shotText = $taskData->shot_number ? ($taskData->sequence_name ? "{$taskData->sequence_name} / {$taskData->shot_number}" : "Shot {$taskData->shot_number}") : null;
                $target = $shotText ?: ($taskData->asset_name ? "Asset {$taskData->asset_name}" : "General Task");
                $thumb  = $taskData->shot_thumb ?: $taskData->asset_thumb;
                $est    = $taskData->estimated_hours ? "{$taskData->estimated_hours} hrs" : 'Not set';
                $due    = $taskData->due_date ? date('d M Y', strtotime($taskData->due_date)) : 'Not set';
                
                $msg = "*Project:* {$taskData->project_name}\n"
                     . "*Target:* {$target}\n"
                     . "*Task:* {$taskData->task_name}\n"
                     . "*Complexity:* {$taskData->complexity}\n"
                     . "*Est. Time:* {$est}\n"
                     . "*Due Date:* {$due}";
                     
                send_notification($assignedTo, 'task_assigned', 'Task Assigned', $msg, '/user/dashboard', $thumb);
            }
        }

        return redirect()->back()->with('message', 'Task assignee updated successfully.');
    }

    public function updateComplexity()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rules = [
            'task_id'    => 'required|is_natural_no_zero',
            'complexity' => 'required|in_list[Simple,Medium,Complex]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Validation failed for updating complexity.');
        }

        $model = new \App\Models\TaskModel();
        
        $taskId = $this->request->getPost('task_id');
        $complexity = $this->request->getPost('complexity');

        $task = $model->find($taskId);
        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        $model->update($taskId, [
            'complexity' => $complexity,
        ]);

        return redirect()->back()->with('message', 'Task complexity updated. Estimated hours recalculated.');
    }

    public function updateSettings()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rules = [
            'task_id'     => 'required|is_natural_no_zero',
            'fps'         => 'permit_empty|is_natural_no_zero',
            'frame_count' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Validation failed for updating task settings.');
        }

        $model = new \App\Models\TaskModel();
        
        $taskId = $this->request->getPost('task_id');
        $fps = $this->request->getPost('fps');
        $frameCount = $this->request->getPost('frame_count');

        $task = $model->find($taskId);
        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        $model->update($taskId, [
            'fps'         => empty($fps) ? null : (int)$fps,
            'frame_count' => empty($frameCount) ? null : (int)$frameCount,
        ]);

        return redirect()->back()->with('message', 'Task settings updated. Estimated hours recalculated.');
    }

    public function reviewStatus($id)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('userRole'), ['admin', 'project_manager'])) {
            return redirect()->to('/login');
        }

        $rules = [
            'status' => 'required|in_list[completed,revision_needed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid review status.');
        }

        $status = $this->request->getPost('status');
        $model = new \App\Models\TaskModel();
        
        $task = $model->find($id);
        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        if ($task->status !== 'ready_for_review') {
            return redirect()->back()->with('error', 'Task is not ready for review.');
        }

        $model->update($id, [
            'status' => $status,
        ]);

        $message = $status === 'completed' ? 'Task Approved & Completed.' : 'Revision Requested from Artist.';

        return redirect()->back()->with('message', $message);
    }

    public function recalculate($id)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('userRole'), ['admin', 'project_manager'])) {
            return redirect()->to('/login');
        }

        $model = new \App\Models\TaskModel();
        $task = $model->find($id);

        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        // Triggering an update with complexity will force the beforeUpdate callback to calculate estimated_hours
        $model->update($id, [
            'complexity' => $task->complexity ?: 'Medium'
        ]);

        return redirect()->back()->with('message', 'Task estimation recalculated.');
    }

    public function bulkRecalculate($projectId)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('userRole'), ['admin', 'project_manager'])) {
            return redirect()->to('/login');
        }

        $model = new \App\Models\TaskModel();
        // Recalculate ALL tasks in this project
        $tasks = $model->where('project_id', $projectId)->findAll();

        $count = 0;
        foreach ($tasks as $task) {
            $model->update($task->id, [
                'complexity' => $task->complexity ?: 'Medium',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $count++;
        }

        return redirect()->back()->with('message', "Recalculated estimated hours for {$count} tasks.");
    }
}
