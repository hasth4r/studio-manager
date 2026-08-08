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

        // Fetch Client's Projects
        $projects = $db->table('projects')
            ->where('client_id', $clientId)
            ->where('status !=', 'completed')
            ->orderBy('created_at', 'DESC')
            ->get()->getResult();

        $projectIds = array_column($projects, 'id');

        // Fetch Recent Pending Reviews for their projects
        $reviews = [];
        $projectStats = [];
        
        if (!empty($projectIds)) {
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
                ->limit(5)
                ->get()->getResult();
                
            // Fetch stats for these projects
            $taskStatsRaw = $db->table('tasks')
                ->select('project_id, status, COUNT(id) as count')
                ->whereIn('project_id', $projectIds)
                ->groupBy('project_id, status')
                ->get()->getResult();

            foreach ($projectIds as $pid) {
                $projectStats[$pid] = [
                    'total' => 0, 'completed' => 0, 'pending' => 0,
                    'in_progress' => 0, 'review' => 0, 'progress' => 0,
                ];
            }

            foreach ($taskStatsRaw as $stat) {
                $pid = $stat->project_id;
                $projectStats[$pid]['total'] += $stat->count;
                
                if ($stat->status === 'completed') {
                    $projectStats[$pid]['completed'] += $stat->count;
                } elseif ($stat->status === 'pending') {
                    $projectStats[$pid]['pending'] += $stat->count;
                } elseif ($stat->status === 'in_progress') {
                    $projectStats[$pid]['in_progress'] += $stat->count;
                } elseif (in_array($stat->status, ['ready_for_review', 'revision_needed'])) {
                    $projectStats[$pid]['review'] += $stat->count;
                }
            }

            foreach ($projectStats as $pid => &$stats) {
                if ($stats['total'] > 0) {
                    $stats['progress'] = round(($stats['completed'] / $stats['total']) * 100);
                }
            }
        }
        
        foreach ($projects as $project) {
            $project->stats = $projectStats[$project->id] ?? null;
        }

        $data = [
            'pageTitle' => 'Client Portal',
            'projects'  => $projects,
            'reviews'   => $reviews,
        ];

        return view('client/dashboard/index', $data);
    }
}
