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
        
        $review = $db->table('reviews r')
            ->select('r.*, p.name as project_name, p.client_id, s.shot_number, u.name as artist_name, vta.id as vfx_task_assignment_id, vta.status as task_status, c.name as task_name, rf.proxy_path, rf.file_type, rf.updated_at as file_updated_at')
            ->join('projects p', 'p.id = r.project_id', 'left')
            ->join('shots s', 's.id = r.shot_id', 'left')
            ->join('tasks vta', 'vta.id = r.vfx_task_assignment_id', 'left')
            ->join('task_types c', 'c.id = vta.task_type_id', 'left')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->join('review_files rf', 'rf.review_id = r.id', 'left')
            ->where('r.id', $reviewId)
            ->get()->getRow();

        if (!$review || $review->client_id != session()->get('clientId')) {
            return redirect()->back()->with('error', 'Review not found or unauthorized.');
        }

        $comments = $db->table('review_comments rc')
            ->select('rc.*, u.name as reviewer_name, u.global_role as reviewer_role')
            ->join('users u', 'u.id = rc.user_id', 'left')
            ->where('rc.review_id', $reviewId)
            ->orderBy('rc.timecode', 'ASC')
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

        if (!$sequence || $sequence->client_id != session()->get('clientId')) {
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
            $latestReview = $db->table('reviews r')
                ->select('r.id as review_id, rf.proxy_path, rf.file_type, r.version_string, c.name as task_name')
                ->join('review_files rf', 'rf.review_id = r.id', 'inner')
                ->join('tasks vta', 'vta.id = r.vfx_task_assignment_id', 'left')
                ->join('task_types c', 'c.id = vta.task_type_id', 'left')
                ->where('r.shot_id', $shot->id)
                ->where('rf.file_type', 'video')
                ->orderBy('r.created_at', 'DESC')
                ->limit(1)
                ->get()->getRow();
                
            if ($latestReview && $latestReview->proxy_path) {
                $playlist[] = [
                    'shot_id' => $shot->id,
                    'shot_number' => $shot->shot_number,
                    'thumbnail_path' => $shot->thumbnail_path ? base_url($shot->thumbnail_path) : '',
                    'review_id' => $latestReview->review_id,
                    'proxy_path' => $latestReview->proxy_path,
                    'proxy_url' => base_url('media/serve/' . $latestReview->proxy_path),
                    'version_string' => $latestReview->version_string,
                    'task_name' => $latestReview->task_name
                ];
                $reviewIds[] = $latestReview->review_id;
            }
        }

        // Fetch comments for all reviews in the playlist
        $comments = [];
        if (!empty($reviewIds)) {
            $comments = $db->table('review_comments rc')
                ->select('rc.*, u.name as reviewer_name, u.global_role as reviewer_role')
                ->join('users u', 'u.id = rc.user_id', 'left')
                ->whereIn('rc.review_id', $reviewIds)
                ->orderBy('rc.timecode', 'ASC')
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
            $latestReview = $db->table('reviews r')
                ->select('r.id as review_id, rf.proxy_path, rf.file_type')
                ->join('review_files rf', 'rf.review_id = r.id', 'inner')
                ->where('r.shot_id', $shot->id)
                ->where('rf.file_type', 'video')
                ->orderBy('r.created_at', 'DESC')
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
