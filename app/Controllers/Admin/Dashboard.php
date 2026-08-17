<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        $userId = session()->get('userId');
        $userRole = session()->get('userRole');

        $db = \Config\Database::connect();
        
        // Fetch tasks for this specific user
        $builder = $db->table('tasks');
        $builder->select('tasks.*, task_types.name as task_name, shots.shot_number, assets.name as asset_name, projects.name as project_name');
        $builder->join('task_types', 'task_types.id = tasks.task_type_id');
        $builder->join('projects', 'projects.id = tasks.project_id');
        $builder->join('shots', 'shots.id = tasks.shot_id', 'left');
        $builder->join('assets', 'assets.id = tasks.asset_id', 'left');
        $builder->where('tasks.assigned_to', $userId);
        $builder->orderBy('tasks.created_at', 'DESC');
        $myTasks = $builder->get()->getResult();

        // Analytics for Admin/PM
        $activeProjectsCount = 0;
        $completedTasksCount = 0;
        $pendingReviewsCount = 0;
        $topProjects = [];
        $latestReview = null;

        if (in_array($userRole, ['admin', 'project_manager'])) {
            // Active Projects
            $activeProjectsCount = $db->table('projects')
                ->where('status !=', 'completed')
                ->countAllResults();

            // Tasks Completed (Last 28 Days)
            $completedTasksCount = $db->table('tasks')
                ->where('status', 'approved') // assuming 'approved' is completed
                ->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-28 days')))
                ->countAllResults();

            // Pending Reviews
            $pendingReviewsCount = $db->table('reviews')
                ->where('status', 'pending')
                ->countAllResults();

            // Top Projects (by number of tasks)
            $topProjects = $db->table('projects p')
                ->select('p.id, p.name, COUNT(t.id) as task_count')
                ->join('tasks t', 't.project_id = p.id', 'left')
                ->groupBy('p.id, p.name')
                ->orderBy('task_count', 'DESC')
                ->limit(3)
                ->get()->getResult();

            // Latest Review
            $latestReview = $db->table('reviews r')
                ->select('r.*, p.name as project_name, u.name as artist_name, c.name as task_name, rf.proxy_path, rf.file_type')
                ->join('projects p', 'p.id = r.project_id', 'left')
                ->join('users u', 'u.id = r.user_id', 'left')
                ->join('tasks t', 't.id = r.vfx_task_assignment_id', 'left')
                ->join('task_types c', 'c.id = t.task_type_id', 'left')
                ->join('review_files rf', 'rf.review_id = r.id', 'left')
                ->orderBy('r.created_at', 'DESC')
                ->limit(1)
                ->get()->getRow();
            // Budget & Pipeline Economics
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

            $allProjects = $db->table('projects')->where('status !=', 'completed')->get()->getResult();
            $totalPipelineBudget = 0.0;
            $totalLockedBudget = 0.0;
            $totalPipelineHours = 0.0;

            foreach ($allProjects as $p) {
                if (!empty($p->agreed_budget) && (float)$p->agreed_budget > 0) {
                    $totalLockedBudget += (float)$p->agreed_budget;
                }
                $tasks = $db->table('tasks')->where('project_id', $p->id)->get()->getResult();
                foreach ($tasks as $t) {
                    $h = (float)($t->estimated_hours ?? 0);
                    $r = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
                    $b = ($h * $r + $h * $opsHourlyRate) * (1 + ($commissionPct / 100.0));
                    $totalPipelineBudget += $b;
                    $totalPipelineHours += $h;
                }
            }
        }

        $data = [
            'userRole'              => $userRole,
            'userName'              => session()->get('userName'),
            'pageTitle'             => 'Dashboard',
            'myTasks'               => $myTasks,
            'activeProjectsCount'   => $activeProjectsCount,
            'completedTasksCount'   => $completedTasksCount,
            'pendingReviewsCount'   => $pendingReviewsCount,
            'topProjects'           => $topProjects,
            'latestReview'          => $latestReview,
            'totalPipelineBudget'   => round($totalPipelineBudget, 0),
            'totalLockedBudget'     => round($totalLockedBudget, 0),
            'totalPipelineHours'    => round($totalPipelineHours, 1),
            'studioCurrency'        => $studioCurrency ?? '₹',
        ];

        return view('dashboard/index', $data);
    }
}
