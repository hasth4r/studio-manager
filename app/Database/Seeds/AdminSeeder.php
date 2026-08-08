<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'          => 'System Admin',
            'email'         => 'admin@enso8.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'global_role'   => 'admin',
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        // Simple check to avoid duplicates if run multiple times
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        if ($builder->where('email', 'admin@enso8.com')->countAllResults() === 0) {
            $builder->insert($data);
        }
    }
}
