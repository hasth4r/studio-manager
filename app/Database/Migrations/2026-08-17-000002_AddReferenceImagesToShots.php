<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReferenceImagesToShots extends Migration
{
    public function up()
    {
        $fields = [
            'reference_images' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'description'
            ],
            'client_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'reference_images'
            ],
        ];

        // Check and add if columns don't exist
        $db = \Config\Database::connect();
        if (!$db->fieldExists('reference_images', 'shots')) {
            $this->forge->addColumn('shots', [
                'reference_images' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'description'
                ]
            ]);
        }
        if (!$db->fieldExists('client_notes', 'shots')) {
            $this->forge->addColumn('shots', [
                'client_notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'reference_images'
                ]
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('shots', ['reference_images', 'client_notes']);
    }
}
