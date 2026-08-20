<?php

namespace App\Controllers\PM;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        helper('auth');
        $userId = (int)session()->get('userId');
        $db = \Config\Database::connect();

        // 1. Fetch Supervised Projects
        $supervisedProjects = $db->table('projects')
            ->select('projects.*, clients.company_name as client_name, 
                     (SELECT COUNT(*) FROM ' . $db->prefixTable('shots') . ' WHERE project_id = ' . $db->prefixTable('projects') . '.id) as shot_count,
                     (SELECT COUNT(*) FROM ' . $db->prefixTable('tasks') . ' WHERE project_id = ' . $db->prefixTable('projects') . '.id) as total_tasks,
                     (SELECT COUNT(*) FROM ' . $db->prefixTable('tasks') . ' WHERE project_id = ' . $db->prefixTable('projects') . '.id AND status = "completed") as completed_tasks')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->where('projects.supervisor_id', $userId)
            ->orWhere('projects.id IN (SELECT project_id FROM ' . $db->prefixTable('sequences') . ' WHERE supervisor_id = ' . $userId . ')')
            ->get()->getResult();

        $supervisedProjIds = array_column($supervisedProjects, 'id');

        // 2. Fetch Pending Reviews in Supervised Projects
        $pendingReviews = [];
        if (!empty($supervisedProjIds)) {
            $pendingReviews = $db->table('reviews')
                ->select('reviews.*, projects.name as project_name, shots.shot_number, users.name as artist_name, task_types.name as task_name, review_files.proxy_path, review_files.file_type')
                ->join('projects', 'projects.id = reviews.project_id', 'left')
                ->join('shots', 'shots.id = reviews.shot_id', 'left')
                ->join('tasks', 'tasks.id = reviews.vfx_task_assignment_id', 'left')
                ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
                ->join('users', 'users.id = reviews.user_id', 'left')
                ->join('review_files', 'review_files.review_id = reviews.id', 'left')
                ->whereIn('reviews.project_id', $supervisedProjIds)
                ->where('reviews.status', 'pending')
                ->orderBy('reviews.created_at', 'DESC')
                ->get()->getResult();
        }

        // 3. Supervised Sequences
        $supervisedSequences = $db->table('sequences')
            ->select('sequences.*, projects.name as project_name, (SELECT COUNT(*) FROM ' . $db->prefixTable('shots') . ' WHERE sequence_id = ' . $db->prefixTable('sequences') . '.id) as shot_count')
            ->join('projects', 'projects.id = sequences.project_id', 'left')
            ->where('sequences.supervisor_id', $userId)
            ->get()->getResult();

        // 4. Personal Tasks (If user is also an artist)
        $myTasks = $db->table('tasks')
            ->select('tasks.*, task_types.name as task_name, projects.name as project_name, shots.shot_number')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('shots', 'shots.id = tasks.shot_id', 'left')
            ->where('tasks.assigned_to', $userId)
            ->where('tasks.status !=', 'completed')
            ->get()->getResult();

        $data = [
            'pageTitle'           => 'Project Manager Hub',
            'supervisedProjects'  => $supervisedProjects,
            'supervisedSequences' => $supervisedSequences,
            'pendingReviews'      => $pendingReviews,
            'myTasks'             => $myTasks,
        ];

        return view('pm/dashboard/index', $data);
    }
}
