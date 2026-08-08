<?php

namespace App\Models;

use CodeIgniter\Model;

class SequenceModel extends Model
{
    protected $table            = 'sequences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id',
        'name',
        'description'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'project_id' => 'required|is_natural_no_zero',
        'name'       => 'required|max_length[150]',
    ];
}
