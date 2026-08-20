<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('userId');
        $userRole = session()->get('userRole');
        $db = \Config\Database::connect();
        
        // Auto-run migrations if reviews table doesn't exist
        if (!$db->tableExists('reviews')) {
            $migrate = \Config\Services::migrations();
            try {
                $migrate->latest();
            } catch (\Throwable $e) {
                // Silently fail or log, so dashboard can still load
                log_message('error', 'Auto-migration failed: ' . $e->getMessage());
            }
        }

        $reviewsTable = $db->prefixTable('reviews');

        $tasksTable = $db->prefixTable('tasks');
        // Fetch assigned tasks for the artist
        $myTasks = $db->table('tasks')
            ->select('tasks.*, task_types.name as task_name, projects.name as project_name, shots.shot_number, shots.thumbnail_path as shot_thumb, sequences.name as sequence_name, assets.name as asset_name, (SELECT id FROM ' . $reviewsTable . ' WHERE ' . $reviewsTable . '.vfx_task_assignment_id = ' . $tasksTable . '.id ORDER BY version_number DESC, created_at DESC LIMIT 1) as latest_review_id')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('shots', 'shots.id = tasks.shot_id', 'left')
            ->join('sequences', 'sequences.id = shots.sequence_id', 'left')
            ->join('assets', 'assets.id = tasks.asset_id', 'left')
            ->where('tasks.assigned_to', $userId)
            ->orderBy('CASE ' . $tasksTable . '.status WHEN "revision_needed" THEN 0 WHEN "in_progress" THEN 1 WHEN "pending" THEN 2 WHEN "ready_for_review" THEN 3 WHEN "completed" THEN 4 ELSE 5 END', 'ASC', false)
            ->get()->getResult();

        // Fetch projects where user is designated supervisor
        $supervisedProjects = $db->table('projects')
            ->select('projects.*, clients.company_name as client_name, (SELECT COUNT(*) FROM ' . $db->prefixTable('shots') . ' WHERE project_id = ' . $db->prefixTable('projects') . '.id) as shot_count')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->where('projects.supervisor_id', $userId)
            ->get()->getResult();

        // Fetch sequences where user is designated sequence lead
        $supervisedSequences = $db->table('sequences')
            ->select('sequences.*, projects.name as project_name, (SELECT COUNT(*) FROM ' . $db->prefixTable('shots') . ' WHERE sequence_id = ' . $db->prefixTable('sequences') . '.id) as shot_count')
            ->join('projects', 'projects.id = sequences.project_id', 'left')
            ->where('sequences.supervisor_id', $userId)
            ->get()->getResult();

        $data = [
            'pageTitle'           => 'My Dashboard',
            'userRole'            => $userRole,
            'myTasks'             => $myTasks,
            'supervisedProjects'  => $supervisedProjects,
            'supervisedSequences' => $supervisedSequences,
        ];

        return view('user/dashboard/index', $data);
    }
}
