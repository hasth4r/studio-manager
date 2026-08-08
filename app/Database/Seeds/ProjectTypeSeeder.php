<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProjectTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Commercial', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Episodic', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Youtube Explainers', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Feature film', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Short film', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];

        $db = \Config\Database::connect();
        $builder = $db->table('project_types');
        
        // Prevent duplicates
        if ($builder->countAllResults() === 0) {
            $builder->insertBatch($data);
        }
    }
}
