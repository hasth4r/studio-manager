<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskBenchmarkModel extends Model
{
    protected $table            = 'task_benchmarks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id',
        'task_type_id',
        'simple_hours',
        'medium_hours',
        'complex_hours'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProjectBenchmarks($projectId)
    {
        return $this->select('task_benchmarks.*, task_types.name as task_type_name')
                    ->join('task_types', 'task_types.id = task_benchmarks.task_type_id', 'left')
                    ->where('project_id', $projectId)
                    ->orderBy('task_types.name', 'ASC')
                    ->findAll();
    }
}
