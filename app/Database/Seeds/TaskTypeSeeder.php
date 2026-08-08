<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Asset Tasks
            ['name' => 'Modeling', 'category' => 'asset', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Sculpting', 'category' => 'asset', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Texturing', 'category' => 'asset', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Shading', 'category' => 'asset', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Rigging', 'category' => 'asset', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Grooming', 'category' => 'asset', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Lookdev', 'category' => 'asset', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            
            // Shot Tasks
            ['name' => 'Animation', 'category' => 'shot', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Layout', 'category' => 'shot', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Lighting', 'category' => 'shot', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Compositing', 'category' => 'shot', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Simulation', 'category' => 'shot', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'FX', 'category' => 'shot', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Motion graphics', 'category' => 'shot', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];

        $db = \Config\Database::connect();
        $builder = $db->table('task_types');
        
        // Prevent duplicates
        if ($builder->countAllResults() === 0) {
            $builder->insertBatch($data);
        }
    }
}
