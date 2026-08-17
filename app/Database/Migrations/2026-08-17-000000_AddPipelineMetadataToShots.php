<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPipelineMetadataToShots extends Migration
{
    public function up()
    {
        $fields = [
            'comp_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'after'      => 'shot_number',
            ],
            'frame_in' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'frame_count',
            ],
            'frame_out' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'frame_in',
            ],
            'duration_seconds' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
                'after'      => 'frame_out',
            ],
            'timecode_in' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'null'       => true,
                'after'      => 'duration_seconds',
            ],
            'timecode_out' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'null'       => true,
                'after'      => 'timecode_in',
            ],
            'width' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'timecode_out',
            ],
            'height' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'width',
            ],
        ];

        $this->forge->addColumn('shots', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('shots', [
            'comp_name',
            'frame_in',
            'frame_out',
            'duration_seconds',
            'timecode_in',
            'timecode_out',
            'width',
            'height',
        ]);
    }
}
