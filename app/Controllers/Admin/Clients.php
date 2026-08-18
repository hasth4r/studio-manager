<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\ClientModel;

class Clients extends BaseController
{
    protected $clientModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
    }

    public function index()
    {
        // Only Admin/Site Manager should manage clients
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $data = [
            'pageTitle' => 'Clients',
            'clients'   => $this->clientModel->orderBy('company_name', 'ASC')->findAll(),
        ];

        return view('clients/index', $data);
    }

    public function create()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $data = [
            'pageTitle' => 'Add Client',
        ];

        return view('clients/create', $data);
    }

    public function store()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'company_name' => 'required|min_length[2]|max_length[255]',
            'email'        => 'permit_empty|valid_email',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'contact_name' => $this->request->getPost('contact_name'),
            'email'        => $this->request->getPost('email'),
            'phone'        => $this->request->getPost('phone'),
        ];

        if ($this->clientModel->insert($data)) {
            return redirect()->to('/clients')->with('message', 'Client created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create client.');
    }
    public function createUser()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $rules = [
            'client_id' => 'required|is_natural_no_zero',
            'name'      => 'required|min_length[2]|max_length[255]',
            'email'     => 'required|valid_email|is_unique[users.email]',
            'password'  => 'required|min_length[5]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validation failed. Ensure email is unique and password is at least 5 chars.');
        }

        $userModel = new \App\Models\UserModel();

        $data = [
            'name'             => $this->request->getPost('name'),
            'email'            => $this->request->getPost('email'),
            'password_hash'    => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'global_role'      => 'client',
            'client_id'        => $this->request->getPost('client_id'),
            'status'           => 'active',
        ];

        if ($userModel->insert($data)) {
            return redirect()->to('/admin/clients')->with('message', 'Client portal user created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create client user.');
    }
}
