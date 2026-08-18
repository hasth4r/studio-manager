<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\ProjectTypeModel;

class ProjectTypes extends BaseController
{
    protected $projectTypeModel;

    public function __construct()
    {
        $this->projectTypeModel = new ProjectTypeModel();
    }

    public function index()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $data = [
            'pageTitle'     => 'Project Types',
            'project_types' => $this->projectTypeModel->orderBy('name', 'ASC')->findAll(),
        ];

        return view('project_types/index', $data);
    }

    public function store()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
        ];

        if ($this->projectTypeModel->insert($data)) {
            return redirect()->to('/project-types')->with('message', 'Project Type created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create Project Type.');
    }
}
