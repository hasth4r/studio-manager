<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class Tasks extends BaseController
{
    public function updateStatus($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('userRole') !== 'artist') {
            return redirect()->to('/login');
        }

        $rules = [
            'status' => 'required|in_list[in_progress,ready_for_review]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid status update.');
        }

        $status = $this->request->getPost('status');
        $userId = session()->get('userId');

        $model = new \App\Models\TaskModel();
        
        $task = $model->find($id);
        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        // Ensure the artist is only updating their own assigned task
        if ($task->assigned_to != $userId) {
            return redirect()->back()->with('error', 'Unauthorized. You can only update tasks assigned to you.');
        }

        // Prevent moving backwards or skipping steps
        if ($status === 'in_progress' && !in_array($task->status, ['pending', 'revision_needed'])) {
            return redirect()->back()->with('error', 'Task cannot be started from its current state.');
        }
        if ($status === 'ready_for_review' && $task->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Task must be in progress before submitting for review.');
        }

        $model->update($id, [
            'status' => $status,
        ]);

        $message = $status === 'in_progress' ? 'Task started successfully.' : 'Task submitted for review.';

        return redirect()->back()->with('message', $message);
    }

    public function updateMeta($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId  = session()->get('userId');
        $model   = new \App\Models\TaskModel();
        $task    = $model->find($id);

        if (!$task || $task->assigned_to != $userId) {
            return redirect()->back()->with('error', 'Unauthorized or task not found.');
        }

        $field   = $this->request->getPost('field');
        $value   = $this->request->getPost('value');

        $allowed = ['due_date', 'estimated_hours', 'notes'];
        if (!in_array($field, $allowed)) {
            return redirect()->back()->with('error', 'Invalid field.');
        }

        $model->update($id, [$field => $value ?: null]);

        return redirect()->back()->with('message', 'Updated successfully.');
    }

    public function submitVersionForm($taskId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $userId = session()->get('userId');
        $db = \Config\Database::connect();
        
        $task = $db->table('tasks')
                   ->select('tasks.*, projects.project_code, shots.shot_number, sequences.name as seq_name, task_types.name as category_name')
                   ->join('projects', 'projects.id = tasks.project_id', 'left')
                   ->join('shots', 'shots.id = tasks.shot_id', 'left')
                   ->join('sequences', 'sequences.id = shots.sequence_id', 'left')
                   ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
                   ->where('tasks.id', $taskId)
                   ->get()->getRow();
                   
        if (!$task || $task->assigned_to != $userId) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        // Get the last review for this task
        $lastReview = $db->table('reviews')
            ->where('vfx_task_assignment_id', $taskId)
            ->orderBy('version_number', 'DESC')
            ->get()->getRow();
            
        $comments = [];
        if ($lastReview) {
            $comments = $db->table('review_comments rc')
                ->select('rc.*, u.name as reviewer_name')
                ->join('users u', 'u.id = rc.user_id', 'left')
                ->where('rc.review_id', $lastReview->id)
                ->where('rc.resolution_status', 'pending')
                ->get()->getResult();
        }
        
        $data['task'] = $task;
        $data['lastReview'] = $lastReview;
        $data['comments'] = $comments;
        
        return view('user/tasks/submit_version', $data);
    }

    public function submitReview()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('userId');
        $taskId = $this->request->getPost('task_id');
        $notes  = $this->request->getPost('artist_notes');
        $file   = $this->request->getFile('review_media');

        $db = \Config\Database::connect();
        
        // 1. Get Task and Verify Ownership
        $task = $db->table('tasks')
                   ->select('tasks.*, projects.project_code, shots.shot_number, sequences.name as seq_name, task_types.name as category_name')
                   ->join('projects', 'projects.id = tasks.project_id', 'left')
                   ->join('shots', 'shots.id = tasks.shot_id', 'left')
                   ->join('sequences', 'sequences.id = shots.sequence_id', 'left')
                   ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
                   ->where('tasks.id', $taskId)
                   ->get()->getRow();

        if (!$task || $task->assigned_to != $userId) {
            return redirect()->back()->with('error', 'Task not found or unauthorized.');
        }

        $versionString = 'V01'; // Default if no file
        $versionNumber = 1;
        $savedFilePath = null;
        $originalFileName = null;
        $fileType = 'none';
        $fileSize = 0;

        // 2. Handle File Upload (If attached)
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $originalFileName = $file->getClientName();
            $fileSize = $file->getSize();
            $mime = $file->getMimeType();
            $fileType = strpos($mime, 'video') !== false ? 'video' : 'image';

            // Parse Version from Filename using Regex
            // Example: JBL-AD_SH001_Comp_ACS_V02.mp4
            if (preg_match('/_V(\d+)\.[a-zA-Z0-9]+$/i', $originalFileName, $matches)) {
                $versionString = 'V' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $versionNumber = (int)$matches[1];
            }

            // Build mirrored folder path inside writable/uploads/
            // e.g. writable/uploads/[Project]/shots/[Seq]/[Shot]/[TaskCategory]/reviews/[Version]/
            $safeProject  = preg_replace('/[^a-zA-Z0-9_-]/', '_', $task->project_code ?? 'Project');
            $safeSeq      = preg_replace('/[^a-zA-Z0-9_-]/', '_', $task->seq_name ?? 'Seq');
            $safeShot     = preg_replace('/[^a-zA-Z0-9_-]/', '_', $task->shot_number ?? 'Shot');
            $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($task->category_name ?? 'Task'));
            
            $mirrorPath = $safeProject . '/shots/' . $safeSeq . '/' . $safeShot . '/' . $safeCategory . '/reviews/' . $versionString;
            $uploadDir  = WRITEPATH . 'uploads/' . $mirrorPath;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $file->move($uploadDir, $originalFileName);
            $savedFilePath = $mirrorPath . '/' . $originalFileName; // Relative path for DB
        }

        // 3. Create Review Record
        $reviewData = [
            'project_id'             => $task->project_id,
            'shot_id'                => $task->shot_id,
            'vfx_task_assignment_id' => $taskId,
            'user_id'                => $userId,
            'version_string'         => $versionString,
            'version_number'         => $versionNumber,
            'status'                 => 'pending',
            'artist_notes'           => $notes,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ];
        $db->table('reviews')->insert($reviewData);
        $reviewId = $db->insertID();

        // 4. Create Review File Record if exists
        if ($savedFilePath) {
            $db->table('review_files')->insert([
                'review_id'         => $reviewId,
                'original_filename' => $originalFileName,
                'proxy_path'        => $savedFilePath, // We'll assume the uploaded file is the proxy for now until FFmpeg is added
                'file_type'         => $fileType,
                'file_size'         => $fileSize,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
            
            // TODO: Trigger background FFmpeg compression task here
        }

        // 4.5. Process Comment Resolutions
        $resolutions = $this->request->getPost('resolutions'); // array of comment_id => status
        $reasons = $this->request->getPost('resolution_reasons'); // array of comment_id => text
        
        if ($resolutions && is_array($resolutions)) {
            foreach ($resolutions as $commentId => $resStatus) {
                if (in_array($resStatus, ['done', 'ignored'])) {
                    $reason = $reasons[$commentId] ?? null;
                    $db->table('review_comments')
                       ->where('id', $commentId)
                       ->update([
                           'resolution_status' => $resStatus,
                           'resolution_comment' => $reason
                       ]);
                }
            }
        }

        // 5. Update Task Status
        $db->table('tasks')
           ->where('id', $taskId)
           ->update([
               'status' => 'ready_for_review'
           ]);

        // 6. Notify Admins/Managers
        helper('notification');
        $admins = $db->table('users')->whereIn('global_role', ['admin', 'project_manager'])->get()->getResult();
        $artistName = session()->get('userName');
        $target = ($task->shot_number ?? $task->asset_name ?? 'Task');
        $msg = "{$artistName} submitted {$versionString} for {$target} ({$task->category_name}).";
        foreach ($admins as $admin) {
            send_notification($admin->id, 'review_submitted', 'Review Submitted', $msg, "/admin/reviews/player/{$reviewId}");
        }

        return redirect()->back()->with('message', 'Task successfully submitted for review!');
    }
}
