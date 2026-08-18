<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgreedBudgetToProjects extends Migration
{
    public function up()
    {
        $fields = [
            'agreed_budget' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'default'    => 0.00,
                'after'      => 'status',
            ],
        ];

        $existingFields = $this->db->getFieldNames('projects');
        $fieldsToAdd = array_diff_key($fields, array_flip($existingFields));
        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('projects', $fieldsToAdd);
        }
    }

    public function down()
    {
        $existingFields = $this->db->getFieldNames('projects');
        if (in_array('agreed_budget', $existingFields)) {
            $this->forge->dropColumn('projects', 'agreed_budget');
        }
    }
}
