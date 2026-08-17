<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'email',
        'password_hash',
        'global_role',
        'status',
        'experience_level',
        'hourly_rate',
        'client_id'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'name'          => 'required|max_length[255]',
        'email'         => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password_hash' => 'required',
        'global_role'   => 'required|in_list[admin,project_manager,artist,client]',
        'status'        => 'required|in_list[active,inactive]',
        'experience_level' => 'permit_empty|in_list[Junior,Mid,Senior]',
        'client_id'     => 'permit_empty|is_natural_no_zero'
    ];
}
