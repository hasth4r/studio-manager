<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateReviewCommentsForResolutions extends Migration
{
    public function up()
    {
        $fields = [
            'resolution_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'pending',
                'null'       => false,
            ],
            'resolution_comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];
        
        $this->forge->addColumn('review_comments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('review_comments', 'resolution_status');
        $this->forge->dropColumn('review_comments', 'resolution_comment');
    }
}
