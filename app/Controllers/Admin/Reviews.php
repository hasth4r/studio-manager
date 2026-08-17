<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Reviews extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        // Auto-run migrations if reviews table doesn't exist
        if (!$db->tableExists('reviews')) {
            $migrate = \Config\Services::migrations();
            try {
                $migrate->latest();
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Migration failed: ' . $e->getMessage());
            }
        }
        
        // Fetch all pending reviews, joining necessary tables to get context
        $query = $db->table('reviews r')
            ->select('r.*, p.name as project_name, p.project_code, s.shot_number, s.thumbnail_path as shot_thumb, seq.name as seq_name, u.name as artist_name, vta.id as vfx_task_assignment_id, c.name as task_name, rf.proxy_path, rf.file_type')
            ->join('projects p', 'p.id = r.project_id', 'left')
            ->join('shots s', 's.id = r.shot_id', 'left')
            ->join('sequences seq', 'seq.id = s.sequence_id', 'left')
            ->join('tasks vta', 'vta.id = r.vfx_task_assignment_id', 'left')
            ->join('task_types c', 'c.id = vta.task_type_id', 'left')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->join('review_files rf', 'rf.review_id = r.id', 'left') // Assume 1 file per review for now
            ->where('r.status', 'pending')
            ->orderBy('r.created_at', 'DESC')
            ->get();

        $data['pending_reviews'] = $query->getResult();
        $data['title'] = "Review Inbox";

        return view('admin/reviews/index', $data);
    }

    public function player($reviewId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        $review = $db->table('reviews r')
            ->select('r.*, p.name as project_name, s.shot_number, seq.name as seq_name, u.name as artist_name, vta.id as vfx_task_assignment_id, vta.status as task_status, c.name as task_name, rf.proxy_path, rf.file_type, rf.updated_at as file_updated_at')
            ->join('projects p', 'p.id = r.project_id', 'left')
            ->join('shots s', 's.id = r.shot_id', 'left')
            ->join('sequences seq', 'seq.id = s.sequence_id', 'left')
            ->join('tasks vta', 'vta.id = r.vfx_task_assignment_id', 'left')
            ->join('task_types c', 'c.id = vta.task_type_id', 'left')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->join('review_files rf', 'rf.review_id = r.id', 'left')
            ->where('r.id', $reviewId)
            ->get()->getRow();

        if (!$review) {
            return redirect()->back()->with('error', 'Review not found.');
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

    public function updateStatus($reviewId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $status = $this->request->getPost('status'); // approved, revision_needed
        $taskId = $this->request->getPost('task_id');

        $db = \Config\Database::connect();
        
        $db->table('reviews')
           ->where('id', $reviewId)
           ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);

        $taskStatus = ($status === 'approved') ? 'completed' : 'revision_needed'; // map back to task status
        
        $db->table('tasks')
           ->where('id', $taskId)
           ->update(['status' => $taskStatus, 'updated_at' => date('Y-m-d H:i:s')]);

        // Send Notification to Artist
        helper('notification');
        $reviewData = $db->table('reviews r')
            ->select('r.user_id, r.version_string, c.name as task_name, s.shot_number, a.name as asset_name')
            ->join('tasks t', 't.id = r.vfx_task_assignment_id', 'left')
            ->join('task_types c', 'c.id = t.task_type_id', 'left')
            ->join('shots s', 's.id = t.shot_id', 'left')
            ->join('assets a', 'a.id = t.asset_id', 'left')
            ->where('r.id', $reviewId)
            ->get()->getRow();

        if ($reviewData && $reviewData->user_id) {
            $target = $reviewData->shot_number ? "Shot {$reviewData->shot_number}" : ($reviewData->asset_name ? "Asset {$reviewData->asset_name}" : "your task");
            $statusText = ($status === 'approved') ? 'Approved' : 'Revision Needed';
            $msg = "Your review ({$reviewData->version_string}) for {$target} ({$reviewData->task_name}) was marked as {$statusText}.";
            send_notification($reviewData->user_id, 'review_status', 'Review ' . $statusText, $msg, '/user/dashboard');
        }

        return redirect()->back()->with('message', 'Review status updated to ' . ucfirst($status));
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
            ->select('sequences.*, p.name as project_name')
            ->join('projects p', 'p.id = sequences.project_id', 'left')
            ->where('sequences.id', $seqId)
            ->get()->getRow();

        if (!$sequence) {
            return redirect()->back()->with('error', 'Sequence not found.');
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
            $duration = !empty($shot->frame_count) ? round((float)$shot->frame_count / $fps, 2) : 1.0;

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
                    'thumbnail_path' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'review_id' => $latestReview->review_id,
                    'proxy_path' => $latestReview->proxy_path,
                    'proxy_url' => media_cdn_url($latestReview->proxy_path),
                    'version_string' => $latestReview->version_string,
                    'task_name' => $latestReview->task_name,
                    'file_type' => 'video',
                    'duration' => $duration
                ];
                $reviewIds[] = $latestReview->review_id;
            } elseif (!empty($shot->preview_video_path)) {
                $playlist[] = [
                    'shot_id' => $shot->id,
                    'shot_number' => $shot->shot_number,
                    'thumbnail_path' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'review_id' => 0,
                    'proxy_path' => $shot->preview_video_path,
                    'proxy_url' => media_cdn_url($shot->preview_video_path),
                    'version_string' => 'Editorial Preview',
                    'task_name' => 'Editorial Lineup',
                    'file_type' => 'video',
                    'duration' => $duration
                ];
            } else {
                $playlist[] = [
                    'shot_id' => $shot->id,
                    'shot_number' => $shot->shot_number,
                    'thumbnail_path' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'review_id' => 0,
                    'proxy_path' => $shot->thumbnail_path,
                    'proxy_url' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'version_string' => 'Still Frame',
                    'task_name' => 'Shot Preview',
                    'file_type' => !empty($shot->thumbnail_path) ? 'image' : 'none',
                    'duration' => $duration
                ];
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
                    'proxy_path' => media_cdn_url($latestReview->proxy_path),
                    'is_preview' => false
                ];
            } elseif (!empty($shot->preview_video_path)) {
                $playlist[] = [
                    'shot_id' => $shot->id,
                    'shot_number' => $shot->shot_number,
                    'review_id' => 0,
                    'proxy_path' => media_cdn_url($shot->preview_video_path),
                    'is_preview' => true
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

    /**
     * Generate Public Share Link with Expiration
     */
    public function createShareLink()
    {
        $sequenceId = (int)$this->request->getPost('sequence_id');
        $expiresIn = $this->request->getPost('expires_in') ?? '7d';
        $customWatermark = trim($this->request->getPost('watermark_text') ?? '');

        if (!$sequenceId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sequence ID is required', 'csrf' => csrf_hash()]);
        }

        $expiresAt = match ($expiresIn) {
            '24h' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            '3d'  => date('Y-m-d H:i:s', strtotime('+3 days')),
            '7d'  => date('Y-m-d H:i:s', strtotime('+7 days')),
            '30d' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'never' => null,
            default => date('Y-m-d H:i:s', strtotime('+7 days'))
        };

        $token = bin2hex(random_bytes(20)); // 40 chars unique token
        $db = \Config\Database::connect();

        // Ensure table exists
        $shareCtrl = new \App\Controllers\Share();
        $reflector = new \ReflectionClass($shareCtrl);
        $method = $reflector->getMethod('ensureTableExists');
        $method->setAccessible(true);
        $method->invoke($shareCtrl, $db);

        $db->table('public_share_tokens')->insert([
            'token'          => $token,
            'resource_type'  => 'sequence',
            'resource_id'    => $sequenceId,
            'watermark_text' => !empty($customWatermark) ? $customWatermark : null,
            'expires_at'     => $expiresAt,
            'created_by'     => session()->get('userId'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $shareUrl = base_url('share/lineup/' . $sequenceId . '?token=' . $token);

        return $this->response->setJSON([
            'status'     => 'success',
            'url'        => $shareUrl,
            'token'      => $token,
            'expires_at' => $expiresAt ? date('M d, Y H:i', strtotime($expiresAt)) : 'Never',
            'csrf'       => csrf_hash(),
        ]);
    }

    /**
     * Get Active Share Links for a Sequence
     */
    public function getShareLinks($sequenceId)
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('public_share_tokens')) {
            return $this->response->setJSON(['status' => 'success', 'links' => []]);
        }

        $links = $db->table('public_share_tokens')
            ->where('resource_type', 'sequence')
            ->where('resource_id', (int)$sequenceId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResult();

        $formatted = array_map(function($l) {
            $isExpired = !empty($l->expires_at) && strtotime($l->expires_at) < time();
            return [
                'id'         => $l->id,
                'token'      => $l->token,
                'url'        => base_url('share/lineup/' . $l->resource_id . '?token=' . $l->token),
                'expires_at' => $l->expires_at ? date('M d, Y H:i', strtotime($l->expires_at)) : 'Never',
                'is_expired' => $isExpired,
                'view_count' => $l->view_count,
                'created_at' => date('M d, Y', strtotime($l->created_at)),
            ];
        }, $links);

        return $this->response->setJSON(['status' => 'success', 'links' => $formatted]);
    }

    /**
     * Revoke Share Link
     */
    public function revokeShareLink()
    {
        $tokenId = (int)$this->request->getPost('token_id');
        if (!$tokenId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Token ID required', 'csrf' => csrf_hash()]);
        }

        $db = \Config\Database::connect();
        $db->table('public_share_tokens')->where('id', $tokenId)->delete();

        return $this->response->setJSON(['status' => 'success', 'csrf' => csrf_hash()]);
    }
}

