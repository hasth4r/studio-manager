<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSchedulerFieldsToTasks extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tasks', [
            'start_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'estimated_hours' => [
                'type' => 'FLOAT',
                'default' => 8,
            ],
            'dependencies' => [
                'type' => 'TEXT', // JSON array of task IDs it depends on
                'null' => true,
            ],
            'priority_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 50,
            ],
            'is_undocked' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tasks', 'start_date');
        $this->forge->dropColumn('tasks', 'end_date');
        $this->forge->dropColumn('tasks', 'estimated_hours');
        $this->forge->dropColumn('tasks', 'dependencies');
        $this->forge->dropColumn('tasks', 'priority_percentage');
        $this->forge->dropColumn('tasks', 'is_undocked');
    }
}
