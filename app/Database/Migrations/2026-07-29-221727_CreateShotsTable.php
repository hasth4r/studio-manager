<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShotsTable extends Migration
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
            'sequence_id' => [
                'type'       => 'INTEGER',
                'null'       => true,
            ],
            'shot_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'thumbnail_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
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
        $this->forge->createTable('shots');
    }

    public function down()
    {
        $this->forge->dropTable('shots');
    }
}
