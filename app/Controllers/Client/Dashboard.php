<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (session()->get('userRole') !== 'client') {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $db = \Config\Database::connect();
        $clientId = session()->get('clientId');

        // Fetch Currency
        $settingsRow = $db->table('settings')->where('key', 'studio_currency')->get()->getRow();
        $currency = $settingsRow ? $settingsRow->value : '$';

        // Fetch Client's Active Projects
        $projects = $db->table('projects')
            ->where('client_id', $clientId)
            ->where('status !=', 'completed')
            ->orderBy('created_at', 'DESC')
            ->get()->getResult();

        $projectIds = array_column($projects, 'id');

        $reviews = [];
        $projectStats = [];
        
        $totalAgreedBudget = 0.0;
        $totalEstimatedHours = 0.0;
        $totalCompletedHours = 0.0;
        $totalTasksCount = 0;
        $totalCompletedTasksCount = 0;
        $totalReviewTasksCount = 0;
        $totalInProgressTasksCount = 0;
        $totalShotsCount = 0;

        if (!empty($projectIds)) {
            // 1. Fetch Recent Submissions / Deliveries
            $reviews = $db->table('reviews r')
                ->select('r.*, s.shot_number, s.thumbnail_path as shot_thumb, seq.name as seq_name, a.name as asset_name, p.name as project_name, u.name as artist_name')
                ->join('tasks t', 't.id = r.vfx_task_assignment_id', 'left')
                ->join('shots s', 's.id = t.shot_id', 'left')
                ->join('sequences seq', 'seq.id = s.sequence_id', 'left')
                ->join('assets a', 'a.id = t.asset_id', 'left')
                ->join('projects p', 'p.id = t.project_id', 'left')
                ->join('users u', 'u.id = r.user_id', 'left')
                ->whereIn('p.id', $projectIds)
                ->whereIn('r.status', ['pending', 'approved', 'revision_needed'])
                ->orderBy('r.created_at', 'DESC')
                ->limit(6)
                ->get()->getResult();

            // 2. Fetch Granular Tasks & Hours per Project
            $tasksRaw = $db->table('tasks')
                ->select('project_id, status, estimated_hours')
                ->whereIn('project_id', $projectIds)
                ->get()->getResult();

            // 3. Fetch Sequences per Project for direct Lineup Player link
            $sequencesRaw = $db->table('sequences')
                ->select('id, project_id, name')
                ->whereIn('project_id', $projectIds)
                ->orderBy('id', 'ASC')
                ->get()->getResult();

            $sequencesByProject = [];
            foreach ($sequencesRaw as $seq) {
                $sequencesByProject[$seq->project_id][] = $seq;
            }

            // 4. Fetch Shots Count per Project
            $shotsCountRaw = $db->table('shots s')
                ->select('p.id as project_id, COUNT(s.id) as shot_count')
                ->join('sequences seq', 'seq.id = s.sequence_id', 'inner')
                ->join('projects p', 'p.id = seq.project_id', 'inner')
                ->whereIn('p.id', $projectIds)
                ->groupBy('p.id')
                ->get()->getResult();

            $shotsCountByProject = [];
            foreach ($shotsCountRaw as $sc) {
                $shotsCountByProject[$sc->project_id] = (int)$sc->shot_count;
                $totalShotsCount += (int)$sc->shot_count;
            }

            // Initialize Project Stats
            foreach ($projectIds as $pid) {
                $projectStats[$pid] = [
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0,
                    'in_progress' => 0,
                    'review' => 0,
                    'progress' => 0,
                    'estimated_hours' => 0.0,
                    'completed_hours' => 0.0,
                    'shots_count' => $shotsCountByProject[$pid] ?? 0,
                    'primary_sequence' => $sequencesByProject[$pid][0] ?? null,
                    'all_sequences' => $sequencesByProject[$pid] ?? []
                ];
            }

            foreach ($tasksRaw as $task) {
                $pid = $task->project_id;
                $hours = (float)($task->estimated_hours ?? 0);
                $status = $task->status;

                $projectStats[$pid]['total']++;
                $projectStats[$pid]['estimated_hours'] += $hours;
                $totalEstimatedHours += $hours;
                $totalTasksCount++;

                if (in_array($status, ['completed', 'approved', 'delivered'])) {
                    $projectStats[$pid]['completed']++;
                    $projectStats[$pid]['completed_hours'] += $hours;
                    $totalCompletedHours += $hours;
                    $totalCompletedTasksCount++;
                } elseif (in_array($status, ['ready_for_review', 'revision_needed', 'in_review'])) {
                    $projectStats[$pid]['review']++;
                    $totalReviewTasksCount++;
                } elseif ($status === 'in_progress') {
                    $projectStats[$pid]['in_progress']++;
                    $totalInProgressTasksCount++;
                } else {
                    $projectStats[$pid]['pending']++;
                }
            }

            // Calculate percentage and totals
            foreach ($projectStats as $pid => &$stats) {
                if ($stats['total'] > 0) {
                    $stats['progress'] = round(($stats['completed'] / $stats['total']) * 100);
                }
            }

            foreach ($projects as $project) {
                $project->stats = $projectStats[$project->id] ?? null;
                $budget = (float)($project->agreed_budget ?? 0);
                $totalAgreedBudget += $budget;
            }
        }

        $overallProgress = $totalTasksCount > 0 ? round(($totalCompletedTasksCount / $totalTasksCount) * 100) : 0;

        $kpis = [
            'total_agreed_budget'     => $totalAgreedBudget,
            'currency'                 => $currency,
            'total_estimated_hours'    => round($totalEstimatedHours, 1),
            'total_completed_hours'    => round($totalCompletedHours, 1),
            'hours_remaining'          => max(0, round($totalEstimatedHours - $totalCompletedHours, 1)),
            'total_tasks'              => $totalTasksCount,
            'completed_tasks'          => $totalCompletedTasksCount,
            'in_review_tasks'          => $totalReviewTasksCount,
            'in_progress_tasks'        => $totalInProgressTasksCount,
            'overall_progress'         => $overallProgress,
            'total_shots'              => $totalShotsCount,
            'active_projects_count'    => count($projects)
        ];

        $data = [
            'pageTitle' => 'Client Portal Dashboard',
            'projects'  => $projects,
            'reviews'   => $reviews,
            'kpis'      => $kpis,
            'currency'  => $currency
        ];

        return view('client/dashboard/index', $data);
    }
}
