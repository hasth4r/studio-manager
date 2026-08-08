<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id',
        'shot_id',
        'asset_id',
        'task_type_id',
        'assigned_to',
        'status',
        'due_date',
        'estimated_hours',
        'notes',
        'complexity',
        'fps',
        'frame_count'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'project_id'   => 'required|is_natural_no_zero',
        'task_type_id' => 'required|is_natural_no_zero',
        'assigned_to'  => 'permit_empty|is_natural_no_zero',
        'status'       => 'required|max_length[50]',
        'complexity'   => 'permit_empty|in_list[Simple,Medium,Complex]'
    ];

    protected $beforeInsert = ['calculateEstimatedHours'];
    protected $beforeUpdate = ['calculateEstimatedHours'];

    protected function calculateEstimatedHours(array $data)
    {
        if (!isset($data['data']['project_id']) || !isset($data['data']['task_type_id']) || !isset($data['data']['assigned_to']) || !isset($data['data']['complexity'])) {
            // Need all 4 to calculate reliably. Alternatively, we could fetch missing from DB if it's an update,
            // but usually we assign them together. Let's do a more robust check:
        }
        
        // If it's an update and not all fields are present, we might need to fetch the current task to get missing info
        $projectId  = $data['data']['project_id'] ?? null;
        $taskTypeId = $data['data']['task_type_id'] ?? null;
        $assignedTo = $data['data']['assigned_to'] ?? null;
        $complexity = $data['data']['complexity'] ?? null;
        $shotId     = $data['data']['shot_id'] ?? null;
        $fps        = $data['data']['fps'] ?? null;
        $frameCount = $data['data']['frame_count'] ?? null;

        if (isset($data['id'])) {
            $existing = $this->db->table($this->table)->where('id', is_array($data['id']) ? $data['id'][0] : $data['id'])->get()->getRowArray();
            if ($existing) {
                $projectId  = $projectId ?: $existing['project_id'];
                $taskTypeId = $taskTypeId ?: $existing['task_type_id'];
                $assignedTo = $assignedTo ?: $existing['assigned_to'];
                $complexity = $complexity ?: ($existing['complexity'] ?: 'Medium');
                $shotId     = $shotId ?: $existing['shot_id'];
                $fps        = $fps ?: $existing['fps'];
                $frameCount = $frameCount ?: $existing['frame_count'];
            }
        }

        if ($projectId && $taskTypeId && $complexity) {
            // Get Benchmark
            $benchmark = $this->db->table('task_benchmarks')
                ->where('project_id', $projectId)
                ->where('task_type_id', $taskTypeId)
                ->get()->getRowArray();

            $baseHours = 0.0;
            if ($benchmark) {
                if ($complexity === 'Simple')  $baseHours = (float)$benchmark['simple_hours'];
                if ($complexity === 'Medium')  $baseHours = (float)$benchmark['medium_hours'];
                if ($complexity === 'Complex') $baseHours = (float)$benchmark['complex_hours'];
            }

            // Calculate duration multiplier if it's a shot task
            $durationMultiplier = 1.0;
            if ($shotId) {
                $shot = $this->db->table('shots')->where('id', $shotId)->get()->getRowArray();
                $project = $this->db->table('projects')->where('id', $projectId)->get()->getRowArray();
                
                $finalFps = $fps ?: ($shot['fps'] ?: ($project['fps'] ?: 24));
                $finalFrameCount = $frameCount ?: ($shot['frame_count'] ?: null);

                if ($finalFrameCount && $finalFps > 0) {
                    $durationMultiplier = $finalFrameCount / $finalFps; // Duration in seconds
                } else {
                    $durationMultiplier = 0.0; // Can't calculate if no frames are provided yet
                }
            }

            // Get User Multiplier
            $multiplier = 1.0; // default Mid
            if ($assignedTo) {
                $user = $this->db->table('users')->where('id', $assignedTo)->get()->getRowArray();
                if ($user && $user['experience_level']) {
                    if ($user['experience_level'] === 'Junior') $multiplier = 1.5;
                    if ($user['experience_level'] === 'Senior') $multiplier = 0.8;
                }
            }

            $estimated = $baseHours * $durationMultiplier * $multiplier;
            $data['data']['estimated_hours'] = $estimated > 0 ? round($estimated, 2) : null;
        }

        return $data;
    }
}
