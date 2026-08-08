<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReviewCommentsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'timecode' => [
                'type'       => 'FLOAT',
                'null'       => true, // The exact timestamp (e.g., 12.45)
            ],
            'comment_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'canvas_data' => [
                'type' => 'LONGTEXT',
                'null' => true, // Serialized JSON or SVG of the drawn overlay
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('review_comments');
    }

    public function down()
    {
        $this->forge->dropTable('review_comments');
    }
}
