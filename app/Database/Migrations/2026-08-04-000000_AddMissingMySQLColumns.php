<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingMySQLColumns extends Migration
{
    public function up()
    {
        // Users table
        $this->forge->addColumn('users', [
            'weekly_hours' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 40,
                'null' => true,
            ],
            'telegram_chat_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'telegram_link_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ]
        ]);

        // Projects table
        $this->forge->addColumn('projects', [
            'fps' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 24,
                'null' => true,
            ]
        ]);

        // Shots table
        $this->forge->addColumn('shots', [
            'fps' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 24,
                'null' => true,
            ],
            'frame_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ]
        ]);

        // Task_types table
        $this->forge->addColumn('task_types', [
            'benchmark_hours_per_second' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'null' => true,
                'default' => 0.0000
            ]
        ]);

        // Tasks table
        $this->forge->addColumn('tasks', [
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fps' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'frame_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'phase_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'gantt_row' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ]
        ]);

        // Review_comments table
        $this->forge->addColumn('review_comments', [
            'parent_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['weekly_hours', 'telegram_chat_id', 'telegram_link_code', 'client_id']);
        $this->forge->dropColumn('projects', ['fps']);
        $this->forge->dropColumn('shots', ['fps', 'frame_count']);
        $this->forge->dropColumn('task_types', ['benchmark_hours_per_second']);
        $this->forge->dropColumn('tasks', ['due_date', 'notes', 'fps', 'frame_count', 'phase_id', 'gantt_row']);
        $this->forge->dropColumn('review_comments', ['parent_id']);
    }
}
