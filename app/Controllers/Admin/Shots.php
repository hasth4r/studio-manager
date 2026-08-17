<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Shots extends BaseController
{
    public function show($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        // Fetch Shot
        $builder = $db->table('shots');
        $builder->select('shots.*, projects.name as project_name, projects.fps as project_fps, sequences.name as sequence_name');
        $builder->join('projects', 'projects.id = shots.project_id');
        $builder->join('sequences', 'sequences.id = shots.sequence_id', 'left');
        $builder->where('shots.id', $id);
        $shot = $builder->get()->getRow();

        if (!$shot) {
            return redirect()->back()->with('error', 'Shot not found.');
        }

        // Fetch tasks assigned to this shot (include user experience level)
        $taskBuilder = $db->table('tasks');
        $taskBuilder->select('tasks.*, task_types.name as task_name, users.name as assigned_user, users.experience_level');
        $taskBuilder->join('task_types', 'task_types.id = tasks.task_type_id');
        $taskBuilder->join('users', 'users.id = tasks.assigned_to', 'left');
        $taskBuilder->where('tasks.shot_id', $id);
        $tasks = $taskBuilder->get()->getResult();

        // Attach pending reviews to tasks
        $taskIds = array_column($tasks, 'id');
        if (!empty($taskIds)) {
            $reviews = $db->table('reviews')
                          ->whereIn('vfx_task_assignment_id', $taskIds)
                          ->where('status', 'pending')
                          ->orderBy('created_at', 'DESC') // get the latest pending review
                          ->get()->getResult();
            
            $reviewMap = [];
            foreach ($reviews as $rev) {
                if (!isset($reviewMap[$rev->vfx_task_assignment_id])) {
                    $reviewMap[$rev->vfx_task_assignment_id] = $rev;
                }
            }
            
            foreach ($tasks as $task) {
                $task->pending_review = $reviewMap[$task->id] ?? null;
            }
        } else {
            foreach ($tasks as $task) {
                $task->pending_review = null;
            }
        }

        // Fetch Shot Task Types for Dropdown
        $taskTypeModel = new \App\Models\TaskTypeModel();
        $taskTypes = $taskTypeModel->where('category', 'shot')->findAll();
        
        // Fetch Users for Assignment
        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();

        // Fetch Benchmarks for this project (keyed by task_type_id)
        $bmRaw = $db->table('task_benchmarks')
            ->where('project_id', $shot->project_id)
            ->get()->getResult();
        $benchmarks = [];
        foreach ($bmRaw as $bm) {
            $benchmarks[$bm->task_type_id] = $bm;
        }

        $data = [
            'pageTitle'  => 'Shot: ' . $shot->shot_number,
            'shot'       => $shot,
            'tasks'      => $tasks,
            'taskTypes'  => $taskTypes,
            'users'      => $users,
            'benchmarks' => $benchmarks,
        ];

        return view('shots/show', $data);
    }

    public function updateSettings($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new \App\Models\ShotModel();
        $shot = $model->find($id);

        if (!$shot) {
            return redirect()->back()->with('error', 'Shot not found.');
        }

        $fps = $this->request->getPost('fps');
        $frameCount = $this->request->getPost('frame_count');
        $frameIn = $this->request->getPost('frame_in');
        $frameOut = $this->request->getPost('frame_out');
        $compName = $this->request->getPost('comp_name');
        $timecodeIn = $this->request->getPost('timecode_in');
        $timecodeOut = $this->request->getPost('timecode_out');
        $width = $this->request->getPost('width');
        $height = $this->request->getPost('height');
        $durationSeconds = $this->request->getPost('duration_seconds');

        $updateData = [
            'fps'              => $fps !== null && $fps !== '' ? (int)$fps : null,
            'frame_count'      => $frameCount !== null && $frameCount !== '' ? (int)$frameCount : null,
            'frame_in'         => $frameIn !== null && $frameIn !== '' ? (int)$frameIn : null,
            'frame_out'        => $frameOut !== null && $frameOut !== '' ? (int)$frameOut : null,
            'comp_name'        => !empty($compName) ? $compName : null,
            'timecode_in'      => !empty($timecodeIn) ? $timecodeIn : null,
            'timecode_out'     => !empty($timecodeOut) ? $timecodeOut : null,
            'width'            => $width !== null && $width !== '' ? (int)$width : null,
            'height'           => $height !== null && $height !== '' ? (int)$height : null,
            'duration_seconds' => $durationSeconds !== null && $durationSeconds !== '' ? (float)$durationSeconds : null,
        ];

        // Handle Video Preview Upload
        $uploadedVid = $this->request->getFile('preview_video');
        if ($uploadedVid && $uploadedVid->isValid() && !$uploadedVid->hasMoved()) {
            $targetVideoDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'shots' . DIRECTORY_SEPARATOR . 'videos';
            if (!is_dir($targetVideoDir)) {
                @mkdir($targetVideoDir, 0777, true);
            }
            $newVidName = $uploadedVid->getRandomName();
            $uploadedVid->move($targetVideoDir, $newVidName);
            $updateData['preview_video_path'] = 'uploads/shots/videos/' . $newVidName;
        }

        // Handle Thumbnail Upload
        $uploadedThumb = $this->request->getFile('thumbnail');
        if ($uploadedThumb && $uploadedThumb->isValid() && !$uploadedThumb->hasMoved()) {
            $targetThumbDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'shots';
            if (!is_dir($targetThumbDir)) {
                @mkdir($targetThumbDir, 0777, true);
            }
            $newThumbName = $uploadedThumb->getRandomName();
            $uploadedThumb->move($targetThumbDir, $newThumbName);
            $updateData['thumbnail_path'] = 'uploads/shots/' . $newThumbName;
        }

        $model->update($id, $updateData);

        return redirect()->back()->with('message', 'Shot settings updated successfully.');
    }
}
