<?php

namespace App\Models;

use CodeIgniter\Model;

class ShotModel extends Model
{
    protected $table            = 'shots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id',
        'sequence_id',
        'shot_number',
        'description',
        'thumbnail_path',
        'fps',
        'frame_count'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'project_id'  => 'required|is_natural_no_zero',
        'sequence_id' => 'permit_empty|is_natural_no_zero',
        'shot_number' => 'required|max_length[50]',
    ];
}
