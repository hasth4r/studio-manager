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
        // Only Site Manager, Admin, or HR can manage users
        if (!has_any_role(['site_manager', 'admin', 'hr'])) {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized access.');
        }

        $data = [
            'pageTitle' => 'Team & Roles Management',
            'users'     => $this->userModel->orderBy('name', 'ASC')->findAll(),
        ];

        return view('users/index', $data);
    }

    public function store()
    {
        if (!has_any_role(['site_manager', 'admin', 'hr'])) {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'name'             => 'required|min_length[2]|max_length[255]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[5]',
            'experience_level' => 'permit_empty'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validation failed. Please check the fields or ensure the email is unique.');
        }

        $postedRoles = $this->request->getPost('roles');
        if (empty($postedRoles)) {
            $fallbackRole = $this->request->getPost('global_role') ?: 'artist';
            $postedRoles = [$fallbackRole];
        } elseif (!is_array($postedRoles)) {
            $postedRoles = [$postedRoles];
        }

        $primaryRole = $postedRoles[0];
        $hourlyRate = $this->request->getPost('hourly_rate');

        $data = [
            'name'             => $this->request->getPost('name'),
            'email'            => $this->request->getPost('email'),
            'password_hash'    => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'global_role'      => $primaryRole,
            'roles'            => json_encode($postedRoles),
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
        if (!has_any_role(['site_manager', 'admin', 'hr'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'name'             => 'required|min_length[2]|max_length[255]',
            'email'            => 'required|valid_email',
            'experience_level' => 'permit_empty',
            'status'           => 'required|in_list[active,inactive]'
        ];

        // Only validate password if it's not empty
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[5]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validation failed. Please check the fields.');
        }

        $postedRoles = $this->request->getPost('roles');
        if (empty($postedRoles)) {
            $fallbackRole = $this->request->getPost('global_role') ?: 'artist';
            $postedRoles = [$fallbackRole];
        } elseif (!is_array($postedRoles)) {
            $postedRoles = [$postedRoles];
        }

        $primaryRole = $postedRoles[0];
        $hourlyRate = $this->request->getPost('hourly_rate');

        $data = [
            'name'             => $this->request->getPost('name'),
            'email'            => $this->request->getPost('email'),
            'global_role'      => $primaryRole,
            'roles'            => json_encode($postedRoles),
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
