<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMultiRolesAndSupervisors extends Migration
{
    public function up()
    {
        // 1. Add 'roles' column to users table
        if (!$this->db->fieldExists('roles', 'users')) {
            $this->forge->addColumn('users', [
                'roles' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'global_role',
                    'comment' => 'JSON array of assigned roles (e.g. ["project_manager", "artist"])'
                ]
            ]);
            
            // Seed existing users with their current global_role as default
            $this->db->query("UPDATE users SET roles = JSON_ARRAY(global_role) WHERE roles IS NULL OR roles = ''");
        }

        // 2. Add 'supervisor_id' to projects table
        if (!$this->db->fieldExists('supervisor_id', 'projects')) {
            $this->forge->addColumn('projects', [
                'supervisor_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'client_id',
                    'comment'    => 'Project Lead / Supervisor user ID'
                ]
            ]);
        }

        // 3. Add 'supervisor_id' to sequences table
        if (!$this->db->fieldExists('supervisor_id', 'sequences')) {
            $this->forge->addColumn('sequences', [
                'supervisor_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'project_id',
                    'comment'    => 'Sequence Lead / Supervisor user ID'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('roles', 'users')) {
            $this->forge->dropColumn('users', 'roles');
        }
        if ($this->db->fieldExists('supervisor_id', 'projects')) {
            $this->forge->dropColumn('projects', 'supervisor_id');
        }
        if ($this->db->fieldExists('supervisor_id', 'sequences')) {
            $this->forge->dropColumn('sequences', 'supervisor_id');
        }
    }
}
