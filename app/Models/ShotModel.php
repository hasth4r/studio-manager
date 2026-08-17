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
        'comp_name',
        'description',
        'thumbnail_path',
        'preview_video_path',
        'fps',
        'frame_count',
        'frame_in',
        'frame_out',
        'duration_seconds',
        'timecode_in',
        'timecode_out',
        'width',
        'height',
        'reference_images',
        'client_notes'
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
