<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExperienceLevelToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'experience_level' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Mid',
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'experience_level');
    }
}
