<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class MediaManager extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        if (!has_any_role(['site_manager', 'admin', 'project_manager']) && !is_any_supervisor()) {
            return redirect()->to('/user/dashboard')->with('error', 'Unauthorized access.');
        }

        $data['title'] = "Media Explorer";
        $data['fullScreen'] = true; // Use full width

        return view('admin/media_manager/index', $data);
    }

    public function getTreeData()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();
        $userId = (int)session()->get('userId');

        // 1. Fetch Projects (scoped by role)
        $projectsQ = $db->table('projects')->select('id, name, project_code');
        if (!has_any_role(['site_manager', 'admin'])) {
            $supervisedProj = $db->table('projects')->select('id')->where('supervisor_id', $userId)->get()->getResultArray();
            $supervisedSeq = $db->table('sequences')->select('project_id')->where('supervisor_id', $userId)->get()->getResultArray();
            $assignedTasks = $db->table('tasks')->select('project_id')->where('assigned_to', $userId)->get()->getResultArray();
            $allowedIds = array_values(array_unique(array_filter(array_merge(
                array_column($supervisedProj, 'id'),
                array_column($supervisedSeq, 'project_id'),
                array_column($assignedTasks, 'project_id')
            ))));
            if (!empty($allowedIds)) {
                $projectsQ->whereIn('id', $allowedIds);
            } else {
                $projectsQ->where('id', -1);
            }
        }
        $projects = $projectsQ->get()->getResultArray();
        
        // 2. Fetch Sequences
        $sequences = $db->table('sequences')->select('id, project_id, name')->get()->getResultArray();
        
        // 3. Fetch Shots
        $shots = $db->table('shots')->select('id, sequence_id, shot_number, thumbnail_path')->get()->getResultArray();
        
        // 4. Fetch Tasks (including Assets for later if needed, but sticking to Shots first)
        $tasks = $db->table('tasks')
            ->select('tasks.id, tasks.shot_id, tasks.asset_id, task_types.name as task_name')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->get()->getResultArray();

        // 5. Fetch Reviews (Versions) and their files
        $reviews = $db->table('reviews')
            ->select('reviews.id, reviews.vfx_task_assignment_id as task_id, reviews.version_string, review_files.proxy_path, review_files.file_type')
            ->join('review_files', 'review_files.review_id = reviews.id', 'left')
            ->get()->getResultArray();

        // Build the tree
        $tree = [];

        // Group sequences by project
        $seqByProj = [];
        foreach ($sequences as $seq) {
            $seqByProj[$seq['project_id']][] = $seq;
        }

        // Group shots by sequence
        $shotsBySeq = [];
        foreach ($shots as $shot) {
            $shotsBySeq[$shot['sequence_id']][] = $shot;
        }

        // Group tasks by shot
        $tasksByShot = [];
        foreach ($tasks as $task) {
            if ($task['shot_id']) {
                $tasksByShot[$task['shot_id']][] = $task;
            }
        }

        // Group reviews by task
        $reviewsByTask = [];
        foreach ($reviews as $review) {
            if ($review['task_id']) {
                $reviewsByTask[$review['task_id']][] = $review;
            }
        }

        foreach ($projects as $proj) {
            $projNode = [
                'id' => 'p_' . $proj['id'],
                'text' => $proj['name'] . ' (' . $proj['project_code'] . ')',
                'type' => 'project',
                'children' => []
            ];

            if (isset($seqByProj[$proj['id']])) {
                foreach ($seqByProj[$proj['id']] as $seq) {
                    $seqNode = [
                        'id' => 'seq_' . $seq['id'],
                        'text' => $seq['name'],
                        'type' => 'sequence',
                        'children' => []
                    ];

                    if (isset($shotsBySeq[$seq['id']])) {
                        foreach ($shotsBySeq[$seq['id']] as $shot) {
                            $shotNode = [
                                'id' => 'shot_' . $shot['id'],
                                'text' => $shot['shot_number'],
                                'type' => 'shot',
                                'children' => []
                            ];

                            if (isset($tasksByShot[$shot['id']])) {
                                foreach ($tasksByShot[$shot['id']] as $task) {
                                    $taskNode = [
                                        'id' => 'task_' . $task['id'],
                                        'text' => $task['task_name'],
                                        'type' => 'task',
                                        'children' => []
                                    ];

                                    if (isset($reviewsByTask[$task['id']])) {
                                        foreach ($reviewsByTask[$task['id']] as $rev) {
                                            $taskNode['children'][] = [
                                                'id' => 'rev_' . $rev['id'],
                                                'text' => $rev['version_string'],
                                                'type' => 'file',
                                                'review_id' => $rev['id'],
                                                'file_path' => $rev['proxy_path'],
                                                'file_type' => $rev['file_type']
                                            ];
                                        }
                                    }
                                    $shotNode['children'][] = $taskNode;
                                }
                            }
                            $seqNode['children'][] = $shotNode;
                        }
                    }
                    $projNode['children'][] = $seqNode;
                }
            }
            $tree[] = $projNode;
        }

        return $this->response->setJSON($tree);
    }

    public function replaceMedia($reviewId)
    {
        if (!session()->get('isLoggedIn') || (!has_any_role(['site_manager', 'admin', 'project_manager']) && !is_any_supervisor())) {
            return redirect()->back()->with('error', 'Unauthorized to replace media.');
        }

        $file = $this->request->getFile('media_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Invalid file upload.');
        }

        $db = \Config\Database::connect();
        
        // Get the review to build the path
        $review = $db->table('reviews')
            ->select('reviews.*, projects.project_code, shots.shot_number, sequences.name as seq_name, task_types.name as category_name')
            ->join('projects', 'projects.id = reviews.project_id', 'left')
            ->join('shots', 'shots.id = reviews.shot_id', 'left')
            ->join('sequences', 'sequences.id = shots.sequence_id', 'left')
            ->join('tasks', 'tasks.id = reviews.vfx_task_assignment_id', 'left')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->where('reviews.id', $reviewId)
            ->get()->getRow();

        if (!$review) {
            return redirect()->back()->with('error', 'Review not found.');
        }

        $originalFileName = $file->getClientName();
        $fileSize = $file->getSize();
        $mime = $file->getMimeType();
        $fileType = strpos($mime, 'video') !== false ? 'video' : 'image';

        // Build mirrored folder path
        $safeProject  = preg_replace('/[^a-zA-Z0-9_-]/', '_', $review->project_code ?? 'Project');
        $safeSeq      = preg_replace('/[^a-zA-Z0-9_-]/', '_', $review->seq_name ?? 'Seq');
        $safeShot     = preg_replace('/[^a-zA-Z0-9_-]/', '_', $review->shot_number ?? 'Shot');
        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($review->category_name ?? 'Task'));
        
        $mirrorPath = $safeProject . '/shots/' . $safeSeq . '/' . $safeShot . '/' . $safeCategory . '/reviews/' . $review->version_string;
        $uploadDir  = WRITEPATH . 'uploads/' . $mirrorPath;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file->move($uploadDir, $originalFileName, true);
        $savedFilePath = $mirrorPath . '/' . $originalFileName;

        // Push to Cloudflare R2 if configured
        $r2 = new \App\Libraries\CloudflareStorage();
        if ($r2->isConfigured()) {
            if ($r2->uploadFile($uploadDir . '/' . $originalFileName, 'uploads/' . $savedFilePath)) {
                // Delete local file to save space if R2 upload succeeded
                unlink($uploadDir . '/' . $originalFileName);
            }
        }

        // Check if review_files record exists
        $existingFile = $db->table('review_files')->where('review_id', $reviewId)->get()->getRow();
        
        if ($existingFile) {
            $db->table('review_files')->where('review_id', $reviewId)->update([
                'original_filename' => $originalFileName,
                'proxy_path'        => $savedFilePath,
                'file_type'         => $fileType,
                'file_size'         => $fileSize,
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        } else {
            $db->table('review_files')->insert([
                'review_id'         => $reviewId,
                'original_filename' => $originalFileName,
                'proxy_path'        => $savedFilePath,
                'file_type'         => $fileType,
                'file_size'         => $fileSize,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->back()->with('message', 'Media replaced successfully.');
    }
}
