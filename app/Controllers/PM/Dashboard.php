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
        $supervisedProjects = $db->table('projects p')
            ->select('p.*, c.company_name as client_name, 
                     (SELECT COUNT(*) FROM ' . $db->prefixTable('shots') . ' WHERE project_id = p.id) as shot_count,
                     (SELECT COUNT(*) FROM ' . $db->prefixTable('tasks') . ' WHERE project_id = p.id) as total_tasks,
                     (SELECT COUNT(*) FROM ' . $db->prefixTable('tasks') . ' WHERE project_id = p.id AND status = "completed") as completed_tasks')
            ->join('clients c', 'c.id = p.client_id', 'left')
            ->where('p.supervisor_id', $userId)
            ->orWhere('p.id IN (SELECT project_id FROM ' . $db->prefixTable('sequences') . ' WHERE supervisor_id = ' . $userId . ')')
            ->get()->getResult();

        $supervisedProjIds = array_column($supervisedProjects, 'id');

        // 2. Fetch Pending Reviews in Supervised Projects
        $pendingReviews = [];
        if (!empty($supervisedProjIds)) {
            $pendingReviews = $db->table('reviews r')
                ->select('r.*, p.name as project_name, s.shot_number, u.name as artist_name, tt.name as task_name, rf.proxy_path, rf.file_type')
                ->join('projects p', 'p.id = r.project_id', 'left')
                ->join('shots s', 's.id = r.shot_id', 'left')
                ->join('tasks t', 't.id = r.vfx_task_assignment_id', 'left')
                ->join('task_types tt', 'tt.id = t.task_type_id', 'left')
                ->join('users u', 'u.id = r.user_id', 'left')
                ->join('review_files rf', 'rf.review_id = r.id', 'left')
                ->whereIn('r.project_id', $supervisedProjIds)
                ->where('r.status', 'pending')
                ->orderBy('r.created_at', 'DESC')
                ->get()->getResult();
        }

        // 3. Supervised Sequences
        $supervisedSequences = $db->table('sequences sq')
            ->select('sq.*, p.name as project_name, (SELECT COUNT(*) FROM ' . $db->prefixTable('shots') . ' WHERE sequence_id = sq.id) as shot_count')
            ->join('projects p', 'p.id = sq.project_id', 'left')
            ->where('sq.supervisor_id', $userId)
            ->get()->getResult();

        // 4. Personal Tasks (If user is also an artist)
        $myTasks = $db->table('tasks t')
            ->select('t.*, tt.name as task_name, p.name as project_name, s.shot_number')
            ->join('task_types tt', 'tt.id = t.task_type_id', 'left')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('shots s', 's.id = t.shot_id', 'left')
            ->where('t.assigned_to', $userId)
            ->where('t.status !=', 'completed')
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
