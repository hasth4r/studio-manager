<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'project_code',
        'client_id',
        'collaborator_id',
        'project_type_id',
        'status',
        'start_date',
        'deadline',
        'priority',
        'fps'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name'            => 'required|max_length[255]',
        'project_code'    => 'required|max_length[50]|is_unique[projects.project_code]',
        'client_id'       => 'required|is_natural_no_zero',
        'collaborator_id' => 'permit_empty|is_natural_no_zero',
        'project_type_id' => 'required|is_natural_no_zero',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
