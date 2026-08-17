<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPreviewVideoPathToShots extends Migration
{
    public function up()
    {
        $fields = [
            'preview_video_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'thumbnail_path',
            ],
        ];

        $this->forge->addColumn('shots', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('shots', 'preview_video_path');
    }
}
