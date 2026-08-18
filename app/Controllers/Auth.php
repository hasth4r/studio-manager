<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function login()
    {
        // Common User Login
        return view('auth/login', ['type' => 'USER', 'action' => '/login']);
    }

    public function adminLogin()
    {
        // Admin Login
        return view('auth/login', ['type' => 'ADMIN', 'action' => '/admin/login']);
    }

    public function processLogin()
    {
        // Strict Input Validation
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[5]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = (string)$this->request->getPost('password');
        $type = $this->request->getPost('type'); // USER or ADMIN

        // Database Query for User
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $user = $builder->where('email', $email)->get()->getRow();

        if ($user && password_verify($password, $user->password_hash)) {
            
            // Extract Multi-Roles
            $roles = [];
            if (!empty($user->roles)) {
                $decoded = json_decode($user->roles, true);
                if (is_array($decoded)) {
                    $roles = $decoded;
                }
            }
            if (empty($roles)) {
                $roles = [$user->global_role ?? 'artist'];
            }

            // RBAC Check for Admin Login Route
            if ($type === 'ADMIN' && !in_array('admin', $roles) && !in_array('site_manager', $roles)) {
                return redirect()->back()->withInput()->with('error', 'Unauthorized. Admin or Site Manager access required.');
            }

            $primaryRole = $roles[0] ?? $user->global_role;

            // Set Session Data
            $session = session();
            $session->set([
                'userId'     => $user->id,
                'userRole'   => $primaryRole,
                'userRoles'  => $roles,
                'userName'   => $user->name,
                'clientId'   => $user->client_id ?? null,
                'isLoggedIn' => true,
            ]);

            if (in_array('site_manager', $roles) || in_array('admin', $roles) || in_array('project_manager', $roles)) {
                return redirect()->to('/admin/dashboard');
            } elseif (in_array('client', $roles)) {
                return redirect()->to('/client/dashboard');
            } else {
                return redirect()->to('/user/dashboard');
            }
        }

        return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
    }
    
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
