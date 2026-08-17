<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class Share extends BaseController
{
    /**
     * Public Lineup Presentation Player (No login required)
     */
    public function lineup($sequenceId = null)
    {
        if (!$sequenceId) {
            throw PageNotFoundException::forPageNotFound('Sequence ID required');
        }

        $token = $this->request->getGet('token');
        if (empty($token)) {
            return view('share/invalid', [
                'title'   => 'Access Denied',
                'message' => 'A valid share token is required to view this presentation.',
            ]);
        }

        $db = \Config\Database::connect();

        // Ensure table exists on first run
        $this->ensureTableExists($db);

        $shareToken = $db->table('public_share_tokens')
            ->where('token', $token)
            ->where('resource_type', 'sequence')
            ->where('resource_id', (int)$sequenceId)
            ->get()->getRow();

        if (!$shareToken) {
            return view('share/invalid', [
                'title'   => 'Invalid Link',
                'message' => 'This presentation link is invalid or has been revoked.',
            ]);
        }

        // Check expiration
        if (!empty($shareToken->expires_at)) {
            if (strtotime($shareToken->expires_at) < time()) {
                return view('share/invalid', [
                    'title'   => 'Link Expired',
                    'message' => 'This review presentation expired on ' . date('M d, Y \a\t h:i A', strtotime($shareToken->expires_at)) . '.',
                    'expired' => true,
                ]);
            }
        }

        // Increment view count
        $db->table('public_share_tokens')
            ->where('id', $shareToken->id)
            ->increment('view_count');

        // Fetch sequence & project
        $sequence = $db->table('sequences s')
            ->select('s.*, p.name as project_name, p.code as project_code')
            ->join('projects p', 'p.id = s.project_id', 'left')
            ->where('s.id', $sequenceId)
            ->get()->getRow();

        if (!$sequence) {
            throw PageNotFoundException::forPageNotFound('Sequence not found');
        }

        // Fetch shots and playlist
        $shots = $db->table('shots')
            ->where('sequence_id', $sequenceId)
            ->orderBy('shot_number', 'ASC')
            ->get()->getResult();

        $playlist = [];
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
                    'shot_id'        => $shot->id,
                    'shot_number'    => $shot->shot_number,
                    'thumbnail_path' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'review_id'      => $latestReview->review_id,
                    'proxy_path'     => $latestReview->proxy_path,
                    'proxy_url'      => media_cdn_url($latestReview->proxy_path),
                    'version_string' => $latestReview->version_string,
                    'task_name'      => $latestReview->task_name,
                    'file_type'      => 'video',
                    'duration'       => $duration,
                ];
            } elseif (!empty($shot->preview_video_path)) {
                $playlist[] = [
                    'shot_id'        => $shot->id,
                    'shot_number'    => $shot->shot_number,
                    'thumbnail_path' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'review_id'      => 0,
                    'proxy_path'     => $shot->preview_video_path,
                    'proxy_url'      => media_cdn_url($shot->preview_video_path),
                    'version_string' => 'Editorial Preview',
                    'task_name'      => 'Editorial Lineup',
                    'file_type'      => 'video',
                    'duration'       => $duration,
                ];
            } else {
                $playlist[] = [
                    'shot_id'        => $shot->id,
                    'shot_number'    => $shot->shot_number,
                    'thumbnail_path' => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'review_id'      => 0,
                    'proxy_path'     => $shot->thumbnail_path,
                    'proxy_url'      => $shot->thumbnail_path ? media_cdn_url($shot->thumbnail_path) : '',
                    'version_string' => 'Still Frame',
                    'task_name'      => 'Shot Preview',
                    'file_type'      => !empty($shot->thumbnail_path) ? 'image' : 'none',
                    'duration'       => $duration,
                ];
            }
        }

        $watermarkText = $shareToken->watermark_text ?: ($sequence->project_name . ' • ' . $sequence->name . ' • Confidential Presentation');

        return view('share/lineup', [
            'title'          => 'Presentation: ' . $sequence->project_name . ' - ' . $sequence->name,
            'sequence'       => $sequence,
            'playlist'       => $playlist,
            'shareToken'     => $shareToken,
            'watermarkText'  => $watermarkText,
            'isPublicShare'  => true,
            'review'         => (object)[
                'project_name'           => $sequence->project_name,
                'shot_number'            => 'Multiple',
                'task_name'              => 'Lineup Presentation',
                'version_string'         => 'Public View',
                'proxy_path'             => !empty($playlist) ? $playlist[0]['proxy_path'] : null,
                'file_type'              => 'video',
                'id'                     => !empty($playlist) ? $playlist[0]['review_id'] : 0,
                'status'                 => 'view_only',
                'artist_notes'           => '',
            ],
        ]);
    }

    private function ensureTableExists($db)
    {
        if (!$db->tableExists('public_share_tokens')) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'unique'     => true,
                ],
                'resource_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['sequence', 'review', 'shot'],
                    'default'    => 'sequence',
                ],
                'resource_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'watermark_text' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'expires_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'view_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('token');
            $forge->addKey(['resource_type', 'resource_id']);
            $forge->createTable('public_share_tokens', true);
        }
    }
}
