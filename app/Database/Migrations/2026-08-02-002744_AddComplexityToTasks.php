<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddComplexityToTasks extends Migration
{
    public function up()
    {
        $fields = [
            'complexity' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Medium',
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('tasks', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tasks', 'complexity');
    }
}
