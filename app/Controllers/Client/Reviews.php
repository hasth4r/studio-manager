<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;

class Reviews extends BaseController
{

    public function player($reviewId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        $review = $db->table('reviews')
            ->select('reviews.*, projects.name as project_name, projects.client_id, shots.shot_number, users.name as artist_name, tasks.id as vfx_task_assignment_id, tasks.status as task_status, task_types.name as task_name, review_files.proxy_path, review_files.file_type, review_files.updated_at as file_updated_at')
            ->join('projects', 'projects.id = reviews.project_id', 'left')
            ->join('shots', 'shots.id = reviews.shot_id', 'left')
            ->join('tasks', 'tasks.id = reviews.vfx_task_assignment_id', 'left')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->join('users', 'users.id = reviews.user_id', 'left')
            ->join('review_files', 'review_files.review_id = reviews.id', 'left')
            ->where('reviews.id', $reviewId)
            ->get()->getRow();

        if (!$review || ($review->client_id != session()->get('clientId') && !has_any_role(['site_manager', 'admin']))) {
            return redirect()->back()->with('error', 'Review not found or unauthorized.');
        }

        $comments = $db->table('review_comments')
            ->select('review_comments.*, users.name as reviewer_name, users.global_role as reviewer_role')
            ->join('users', 'users.id = review_comments.user_id', 'left')
            ->where('review_comments.review_id', $reviewId)
            ->orderBy('review_comments.timecode', 'ASC')
            ->get()->getResult();

        $data['review'] = $review;
        $data['comments'] = $comments;
        
        $data['versions'] = $db->table('reviews')
            ->select('id, version_string, version_number, status, created_at')
            ->where('vfx_task_assignment_id', $review->vfx_task_assignment_id)
            ->orderBy('version_number', 'DESC')
            ->get()->getResult();
            
        $data['userRole'] = session()->get('userRole');
        $data['title'] = "Review Player - " . $review->project_name;

        return view('admin/reviews/player', $data);
    }

    public function saveAnnotation($reviewId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();
        
        $data = [
            'review_id'    => $reviewId,
            'user_id'      => session()->get('userId'),
            'timecode'     => $this->request->getPost('timecode') !== '' ? $this->request->getPost('timecode') : null,
            'comment_text' => $this->request->getPost('comment_text'),
            'canvas_data'  => $this->request->getPost('canvas_data'),
            'parent_id'    => $this->request->getPost('parent_id') !== '' ? $this->request->getPost('parent_id') : null,
            'created_at'   => date('Y-m-d H:i:s')
        ];

        $db->table('review_comments')->insert($data);
        $commentId = $db->insertID();

        return $this->response->setJSON([
            'status' => 'success',
            'comment_id' => $commentId,
            'message' => 'Annotation saved successfully',
            'csrf' => csrf_hash()
        ]);
    }


    public function uploadReference()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $file = $this->request->getFile('reference_image');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid file', 'csrf' => csrf_hash()]);
        }

        $uploadDir = WRITEPATH . 'uploads/references/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        // Push to Cloudflare R2 if configured
        $r2 = new \App\Libraries\CloudflareStorage();
        if ($r2->isConfigured()) {
            if ($r2->uploadFile($uploadDir . $newName, 'uploads/references/' . $newName)) {
                // Delete local file to save space if R2 upload succeeded
                unlink($uploadDir . $newName);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'url' => 'references/' . $newName,
            'csrf' => csrf_hash()
        ]);
    }

    public function updateComment($commentId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();
        $userId = session()->get('userId');
        
        // Verify ownership
        $comment = $db->table('review_comments')->where('id', $commentId)->get()->getRow();
        if (!$comment || $comment->user_id != $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized to edit this comment']);
        }

        $text = $this->request->getPost('comment_text');
        $parentId = $this->request->getPost('parent_id');
        
        $updateData = [
            'comment_text' => $text, 
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($parentId !== null && $parentId !== '') {
            $updateData['parent_id'] = $parentId;
        }

        $db->table('review_comments')->where('id', $commentId)->update($updateData);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteComment($commentId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();
        $userId = session()->get('userId');
        
        // Verify ownership
        $comment = $db->table('review_comments')->where('id', $commentId)->get()->getRow();
        if (!$comment || $comment->user_id != $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized to delete this comment']);
        }

        $db->table('review_comments')->where('id', $commentId)->delete();

        return $this->response->setJSON(['status' => 'success']);
    }

    public function sequencePlayer($seqId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        $sequence = $db->table('sequences')
            ->select('sequences.*, p.name as project_name, p.client_id')
            ->join('projects p', 'p.id = sequences.project_id', 'left')
            ->where('sequences.id', $seqId)
            ->get()->getRow();

        if (!$sequence || ($sequence->client_id != session()->get('clientId') && !has_any_role(['site_manager', 'admin']))) {
            return redirect()->back()->with('error', 'Sequence not found or unauthorized.');
        }

        $userRole = session()->get('userRole');

        // Fetch playlist logic
        $shots = $db->table('shots')
            ->where('sequence_id', $seqId)
            ->orderBy('shot_number', 'ASC')
            ->get()->getResult();

        $playlist = [];
        $reviewIds = [];
        
        foreach ($shots as $shot) {
            $fps = !empty($shot->fps) ? (float)$shot->fps : 24.0;
            $duration = !empty($shot->frame_count) ? round((float)$shot->frame_count / $fps, 2) : 0.0;

            $latestReview = $db->table('reviews')
                ->select('reviews.id as review_id, review_files.proxy_path, review_files.file_type, reviews.version_string, task_types.name as task_name')
                ->join('review_files', 'review_files.review_id = reviews.id', 'inner')
                ->join('tasks', 'tasks.id = reviews.vfx_task_assignment_id', 'left')
                ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
                ->where('reviews.shot_id', $shot->id)
                ->where('review_files.file_type', 'video')
                ->orderBy('reviews.created_at', 'DESC')
                ->limit(1)
                ->get()->getRow();
                
            if ($latestReview && $latestReview->proxy_path) {
                $playlist[] = [
                    'shot_id' => $shot->id,
                    'shot_number' => $shot->shot_number,
                    'thumbnail_path' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'review_id' => $latestReview->review_id,
                    'proxy_path' => $latestReview->proxy_path,
                    'proxy_url' => media_cdn_url($latestReview->proxy_path),
                    'version_string' => $latestReview->version_string,
                    'task_name' => $latestReview->task_name,
                    'duration' => $duration
                ];
                $reviewIds[] = $latestReview->review_id;
            }
        }

        // Fetch comments for all reviews in the playlist
        $comments = [];
        if (!empty($reviewIds)) {
            $comments = $db->table('review_comments')
                ->select('review_comments.*, users.name as reviewer_name, users.global_role as reviewer_role')
                ->join('users', 'users.id = review_comments.user_id', 'left')
                ->whereIn('review_comments.review_id', $reviewIds)
                ->orderBy('review_comments.timecode', 'ASC')
                ->get()->getResult();
        }

        $data = [
            'title' => 'Sequence Lineup: ' . $sequence->name,
            'sequence' => $sequence,
            'userRole' => $userRole,
            'isSequenceMode' => true,
            'playlist' => $playlist,
            'comments' => $comments,
            // dummy review object to prevent errors in player.php
            'review' => (object)[
                'project_name' => $sequence->project_name,
                'shot_number' => 'Multiple',
                'artist_name' => 'Various',
                'vfx_task_assignment_id' => 0,
                'task_status' => 'Sequence',
                'task_name' => 'Lineup',
                'proxy_path' => !empty($playlist) ? $playlist[0]['proxy_path'] : null,
                'file_type' => 'video',
                'id' => !empty($playlist) ? $playlist[0]['review_id'] : 0,
                'version_string' => 'Sequence',
                'status' => 'pending',
                'artist_notes' => ''
            ]
        ];

        return view('admin/reviews/player', $data);
    }

    public function getSequenceData($seqId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();
        
        $shots = $db->table('shots')
            ->where('sequence_id', $seqId)
            ->orderBy('shot_number', 'ASC')
            ->get()->getResult();

        $playlist = [];
        
        foreach ($shots as $shot) {
            $latestReview = $db->table('reviews')
                ->select('reviews.id as review_id, review_files.proxy_path, review_files.file_type')
                ->join('review_files', 'review_files.review_id = reviews.id', 'inner')
                ->where('reviews.shot_id', $shot->id)
                ->where('review_files.file_type', 'video')
                ->orderBy('reviews.created_at', 'DESC')
                ->limit(1)
                ->get()->getRow();
                
            if ($latestReview && $latestReview->proxy_path) {
                $playlist[] = [
                    'shot_id' => $shot->id,
                    'shot_number' => $shot->shot_number,
                    'review_id' => $latestReview->review_id,
                    'proxy_path' => base_url($latestReview->proxy_path),
                ];
            } else {
                $playlist[] = [
                    'shot_id' => $shot->id,
                    'shot_number' => $shot->shot_number,
                    'review_id' => null,
                    'proxy_path' => null, // Missing media
                ];
            }
        }

        return $this->response->setJSON(['status' => 'success', 'playlist' => $playlist]);
    }
}
