<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskTypeModel extends Model
{
    protected $table            = 'task_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'category',
        'default_hourly_rate'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'name'     => 'required|max_length[100]',
        'category' => 'required|max_length[50]',
    ];
}
