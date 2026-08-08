<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskBenchmarksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'project_id' => [
                'type' => 'INTEGER',
            ],
            'task_type_id' => [
                'type' => 'INTEGER',
            ],
            'simple_hours' => [
                'type'    => 'REAL',
                'default' => 0.0,
            ],
            'medium_hours' => [
                'type'    => 'REAL',
                'default' => 0.0,
            ],
            'complex_hours' => [
                'type'    => 'REAL',
                'default' => 0.0,
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
        $this->forge->createTable('task_benchmarks');
    }

    public function down()
    {
        $this->forge->dropTable('task_benchmarks');
    }
}
