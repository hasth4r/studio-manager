<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ClientModel;
use App\Models\CollaboratorModel;

class Api extends ResourceController
{
    public function createClient()
    {
        $role = session()->get('userRole');
        if (!in_array($role, ['admin', 'project_manager'])) {
            return $this->failUnauthorized('Unauthorized access.');
        }

        $rules = [
            'company_name' => 'required|min_length[2]|max_length[255]',
            'email'        => 'permit_empty|valid_email',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $clientModel = new ClientModel();
        
        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'contact_name' => $this->request->getPost('contact_name'),
            'email'        => $this->request->getPost('email'),
            'phone'        => $this->request->getPost('phone'),
        ];

        if ($clientModel->insert($data)) {
            $id = $clientModel->getInsertID();
            return $this->respondCreated(['id' => $id, 'company_name' => $data['company_name']]);
        }

        return $this->failServerError('Failed to create client.');
    }

    public function createCollaborator()
    {
        $role = session()->get('userRole');
        if (!in_array($role, ['admin', 'project_manager'])) {
            return $this->failUnauthorized('Unauthorized access.');
        }

        $rules = [
            'company_name' => 'required|min_length[2]|max_length[255]',
            'email'        => 'permit_empty|valid_email',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $collaboratorModel = new CollaboratorModel();
        
        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'contact_name' => $this->request->getPost('contact_name'),
            'email'        => $this->request->getPost('email'),
            'phone'        => $this->request->getPost('phone'),
        ];

        if ($collaboratorModel->insert($data)) {
            $id = $collaboratorModel->getInsertID();
            return $this->respondCreated(['id' => $id, 'company_name' => $data['company_name']]);
        }

        return $this->failServerError('Failed to create collaborator.');
    }
}
