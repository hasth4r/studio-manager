<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReviewFilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'review_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'original_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'proxy_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true, // The path to the compressed mp4 or image
            ],
            'file_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50', // 'video', 'image'
            ],
            'file_size' => [
                'type'       => 'BIGINT',
                'null'       => true, // Size of the proxy
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
        $this->forge->addForeignKey('review_id', 'reviews', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('review_files');
    }

    public function down()
    {
        $this->forge->dropTable('review_files');
    }
}
