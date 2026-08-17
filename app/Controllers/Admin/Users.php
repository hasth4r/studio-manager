<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Only Admin should manage users
        if (session()->get('userRole') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized access. Admins only.');
        }

        $data = [
            'pageTitle' => 'Team Management',
            'users'     => $this->userModel->orderBy('name', 'ASC')->findAll(),
        ];

        return view('users/index', $data);
    }

    public function store()
    {
        if (session()->get('userRole') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'name'             => 'required|min_length[2]|max_length[255]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[5]',
            'global_role'      => 'required|in_list[admin,project_manager,artist,client]',
            'experience_level' => 'permit_empty|in_list[Junior,Mid,Senior]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validation failed. Please check the fields or ensure the email is unique.');
        }

        $hourlyRate = $this->request->getPost('hourly_rate');
        $data = [
            'name'             => $this->request->getPost('name'),
            'email'            => $this->request->getPost('email'),
            'password_hash'    => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'global_role'      => $this->request->getPost('global_role'),
            'experience_level' => $this->request->getPost('experience_level') ?: 'Mid',
            'hourly_rate'      => $hourlyRate !== '' && $hourlyRate !== null ? (float)$hourlyRate : 500.00,
            'status'           => 'active',
        ];

        if ($this->userModel->insert($data)) {
            return redirect()->to('/admin/users')->with('message', 'User added successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to add user.');
    }

    public function update($id)
    {
        if (session()->get('userRole') !== 'admin') {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'name'             => 'required|min_length[2]|max_length[255]',
            'email'            => 'required|valid_email',
            'global_role'      => 'required|in_list[admin,project_manager,artist,client]',
            'experience_level' => 'permit_empty|in_list[Junior,Mid,Senior]',
            'status'           => 'required|in_list[active,inactive]'
        ];

        // Only validate password if it's not empty
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[5]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validation failed. Please check the fields.');
        }

        $hourlyRate = $this->request->getPost('hourly_rate');
        $data = [
            'name'             => $this->request->getPost('name'),
            'email'            => $this->request->getPost('email'),
            'global_role'      => $this->request->getPost('global_role'),
            'experience_level' => $this->request->getPost('experience_level') ?: 'Mid',
            'hourly_rate'      => $hourlyRate !== '' && $hourlyRate !== null ? (float)$hourlyRate : 500.00,
            'status'           => $this->request->getPost('status'),
        ];

        if ($this->request->getPost('password')) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $data)) {
            return redirect()->to('/admin/users')->with('message', 'User updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update user.');
    }
}
