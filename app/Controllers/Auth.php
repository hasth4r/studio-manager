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
            
            // RBAC Check for Admin Login Route
            if ($type === 'ADMIN' && $user->global_role !== 'admin') {
                return redirect()->back()->withInput()->with('error', 'Unauthorized. Admin access required.');
            }

            // Set Session Data
            $session = session();
            $session->set([
                'userId'     => $user->id,
                'userRole'   => $user->global_role,
                'userName'   => $user->name,
                'clientId'   => $user->client_id ?? null,
                'isLoggedIn' => true,
            ]);

            if (in_array($user->global_role, ['admin', 'project_manager'])) {
                return redirect()->to('/admin/dashboard');
            } elseif ($user->global_role === 'client') {
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
