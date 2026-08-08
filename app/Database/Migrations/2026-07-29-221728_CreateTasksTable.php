<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTasksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'       => 'INTEGER',
            ],
            'shot_id' => [
                'type'       => 'INTEGER',
                'null'       => true, // Null if it's an asset task
            ],
            'asset_id' => [
                'type'       => 'INTEGER',
                'null'       => true, // Null if it's a shot task
            ],
            'task_type_id' => [
                'type'       => 'INTEGER',
            ],
            'assigned_to' => [
                'type'       => 'INTEGER',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tasks');
    }

    public function down()
    {
        $this->forge->dropTable('tasks');
    }
}
