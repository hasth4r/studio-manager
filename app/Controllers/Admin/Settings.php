<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\SettingsModel;

class Settings extends BaseController
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    public function index()
    {
        if (session()->get('userRole') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized access.');
        }

        $data = [
            'pageTitle'             => 'Server Settings',
            'production_drive_path' => $this->settingsModel->getSetting('production_drive_path', 'F:\\STUDIO_PRODUCTION\\PROJECTS'),
        ];

        return view('settings/index', $data);
    }

    public function update()
    {
        if (session()->get('userRole') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized access.');
        }

        $path = $this->request->getPost('production_drive_path');
        
        // Strip trailing slash if present
        $path = rtrim($path, '/\\');

        $this->settingsModel->setSetting('production_drive_path', $path);

        return redirect()->back()->with('message', 'Settings updated successfully.');
    }
}
