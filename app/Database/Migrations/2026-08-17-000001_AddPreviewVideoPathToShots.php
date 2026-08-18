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

        $existingFields = $this->db->getFieldNames('shots');
        $fieldsToAdd = array_diff_key($fields, array_flip($existingFields));
        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('shots', $fieldsToAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('shots', 'preview_video_path');
    }
}
