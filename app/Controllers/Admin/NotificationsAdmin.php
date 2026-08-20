<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class NotificationsAdmin extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || !has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Generate Notifications',
            'userRole' => session()->get('userRole')
        ];

        return view('admin/notifications/generate', $data);
    }

    public function process()
    {
        if (!session()->get('isLoggedIn') || !has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        helper('notification');
        
        $count = 0;

        // 1. Retroactive Task Assignments
        $tasks = $db->table('tasks')
            ->select('tasks.assigned_to, task_types.name as task_name, shots.shot_number, assets.name as asset_name, projects.name as project_name')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->join('shots', 'shots.id = tasks.shot_id', 'left')
            ->join('assets', 'assets.id = tasks.asset_id', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('tasks.assigned_to IS NOT NULL')
            ->where('tasks.status !=', 'completed')
            ->get()->getResult();

        foreach ($tasks as $task) {
            $target = $task->shot_number ? "Shot {$task->shot_number}" : ($task->asset_name ? "Asset {$task->asset_name}" : "a task");
            $msg = "You have been assigned to {$task->task_name} for {$target} in {$task->project_name}.";
            send_notification($task->assigned_to, 'task_assigned', 'Task Assigned (Retroactive)', $msg, '/user/dashboard');
            $count++;
        }

        // 2. Retroactive Review Submissions (Pending Reviews)
        $pendingReviews = $db->table('reviews')
            ->select('reviews.id as review_id, reviews.version_string, task_types.name as task_name, shots.shot_number, assets.name as asset_name, users.name as artist_name')
            ->join('tasks', 'tasks.id = reviews.vfx_task_assignment_id', 'left')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->join('shots', 'shots.id = tasks.shot_id', 'left')
            ->join('assets', 'assets.id = tasks.asset_id', 'left')
            ->join('users', 'users.id = reviews.user_id', 'left')
            ->where('reviews.status', 'pending')
            ->get()->getResult();

        $admins = $db->table('users')->whereIn('global_role', ['admin', 'project_manager'])->get()->getResult();

        foreach ($pendingReviews as $rev) {
            $target = $rev->shot_number ? "Shot {$rev->shot_number}" : ($rev->asset_name ? "Asset {$rev->asset_name}" : "Task");
            $msg = "{$rev->artist_name} submitted {$rev->version_string} for {$target} ({$rev->task_name}).";
            foreach ($admins as $admin) {
                send_notification($admin->id, 'review_submitted', 'Review Submitted (Retroactive)', $msg, "/reviews/player/{$rev->review_id}");
                $count++;
            }
        }

        return redirect()->back()->with('message', "Successfully generated {$count} retroactive notifications for existing tasks and pending reviews!");
    }
}
