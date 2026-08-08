<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStagesTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'order_index' => [
                'type'       => 'INTEGER',
                'default'    => 0,
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
        $this->forge->createTable('stages');
    }

    public function down()
    {
        $this->forge->dropTable('stages');
    }
}
