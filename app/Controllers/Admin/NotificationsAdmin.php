<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class NotificationsAdmin extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('userRole') !== 'admin') {
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
        if (!session()->get('isLoggedIn') || session()->get('userRole') !== 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        helper('notification');
        
        $count = 0;

        // 1. Retroactive Task Assignments
        $tasks = $db->table('tasks t')
            ->select('t.assigned_to, c.name as task_name, s.shot_number, a.name as asset_name, p.name as project_name')
            ->join('task_types c', 'c.id = t.task_type_id', 'left')
            ->join('shots s', 's.id = t.shot_id', 'left')
            ->join('assets a', 'a.id = t.asset_id', 'left')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->where('t.assigned_to IS NOT NULL')
            ->where('t.status !=', 'completed')
            ->get()->getResult();

        foreach ($tasks as $task) {
            $target = $task->shot_number ? "Shot {$task->shot_number}" : ($task->asset_name ? "Asset {$task->asset_name}" : "a task");
            $msg = "You have been assigned to {$task->task_name} for {$target} in {$task->project_name}.";
            send_notification($task->assigned_to, 'task_assigned', 'Task Assigned (Retroactive)', $msg, '/user/dashboard');
            $count++;
        }

        // 2. Retroactive Review Submissions (Pending Reviews)
        $pendingReviews = $db->table('reviews r')
            ->select('r.id as review_id, r.version_string, c.name as task_name, s.shot_number, a.name as asset_name, u.name as artist_name')
            ->join('tasks t', 't.id = r.vfx_task_assignment_id', 'left')
            ->join('task_types c', 'c.id = t.task_type_id', 'left')
            ->join('shots s', 's.id = t.shot_id', 'left')
            ->join('assets a', 'a.id = t.asset_id', 'left')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->where('r.status', 'pending')
            ->get()->getResult();

        $admins = $db->table('users')->whereIn('global_role', ['admin', 'project_manager'])->get()->getResult();

        foreach ($pendingReviews as $rev) {
            $target = $rev->shot_number ? "Shot {$rev->shot_number}" : ($rev->asset_name ? "Asset {$rev->asset_name}" : "Task");
            $msg = "{$rev->artist_name} submitted {$rev->version_string} for {$target} ({$rev->task_name}).";
            foreach ($admins as $admin) {
                send_notification($admin->id, 'review_submitted', 'Review Submitted (Retroactive)', $msg, "/admin/reviews/player/{$rev->review_id}");
                $count++;
            }
        }

        return redirect()->back()->with('message', "Successfully generated {$count} retroactive notifications for existing tasks and pending reviews!");
    }
}
