<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Assets extends BaseController
{
    public function show($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        // Fetch Asset
        $builder = $db->table('assets');
        $builder->select('assets.*, projects.name as project_name');
        $builder->join('projects', 'projects.id = assets.project_id');
        $builder->where('assets.id', $id);
        $asset = $builder->get()->getRow();

        if (!$asset) {
            return redirect()->back()->with('error', 'Asset not found.');
        }

        // Fetch tasks assigned to this asset
        $taskBuilder = $db->table('tasks');
        $taskBuilder->select('tasks.*, task_types.name as task_name, users.name as assigned_user');
        $taskBuilder->join('task_types', 'task_types.id = tasks.task_type_id');
        $taskBuilder->join('users', 'users.id = tasks.assigned_to', 'left');
        $taskBuilder->where('tasks.asset_id', $id);
        $tasks = $taskBuilder->get()->getResult();

        // Fetch Asset Task Types for Dropdown
        $taskTypeModel = new \App\Models\TaskTypeModel();
        $taskTypes = $taskTypeModel->where('category', 'asset')->findAll();
        
        // Fetch Users for Assignment
        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();

        $data = [
            'pageTitle' => 'Asset: ' . $asset->name,
            'asset'     => $asset,
            'tasks'     => $tasks,
            'taskTypes' => $taskTypes,
            'users'     => $users,
        ];

        return view('assets/show', $data);
    }
}
