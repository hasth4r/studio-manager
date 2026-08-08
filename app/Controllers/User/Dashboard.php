<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('userRole') !== 'artist') {
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

        // Fetch assigned tasks for the artist
        $myTasks = $db->table('tasks t')
            ->select('t.*, tt.name as task_name, p.name as project_name, s.shot_number, s.thumbnail_path as shot_thumb, sq.name as sequence_name, a.name as asset_name, (SELECT id FROM ' . $reviewsTable . ' WHERE ' . $reviewsTable . '.vfx_task_assignment_id = t.id ORDER BY version_number DESC, created_at DESC LIMIT 1) as latest_review_id')
            ->join('task_types tt', 'tt.id = t.task_type_id', 'left')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('shots s', 's.id = t.shot_id', 'left')
            ->join('sequences sq', 'sq.id = s.sequence_id', 'left')
            ->join('assets a', 'a.id = t.asset_id', 'left')
            ->where('t.assigned_to', $userId)
            ->orderBy('CASE t.status WHEN "revision_needed" THEN 0 WHEN "in_progress" THEN 1 WHEN "pending" THEN 2 WHEN "ready_for_review" THEN 3 WHEN "completed" THEN 4 ELSE 5 END', 'ASC', false)
            ->get()->getResult();

        $data = [
            'pageTitle' => 'My Dashboard',
            'userRole'  => $userRole,
            'myTasks'   => $myTasks,
        ];

        return view('user/dashboard/index', $data);
    }
}
