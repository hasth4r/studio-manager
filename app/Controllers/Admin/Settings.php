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
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $data = [
            'pageTitle'              => 'Server & Studio Economics Settings',
            'production_drive_path'  => $this->settingsModel->getSetting('production_drive_path', 'F:\\STUDIO_PRODUCTION\\PROJECTS'),
            'studio_currency'        => $this->settingsModel->getSetting('studio_currency', '₹'),
            'studio_ops_hourly_rate' => $this->settingsModel->getSetting('studio_ops_hourly_rate', '100.00'),
            'studio_commission_pct'  => $this->settingsModel->getSetting('studio_commission_pct', '30'),
            'default_artist_rate'    => $this->settingsModel->getSetting('default_artist_rate', '500.00'),
        ];

        return view('settings/index', $data);
    }

    public function update()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $path = $this->request->getPost('production_drive_path');
        if ($path !== null) {
            $path = rtrim($path, '/\\');
            $this->settingsModel->setSetting('production_drive_path', $path);
        }

        $currency = $this->request->getPost('studio_currency');
        if ($currency !== null) {
            $this->settingsModel->setSetting('studio_currency', trim($currency));
        }

        $opsRate = $this->request->getPost('studio_ops_hourly_rate');
        if ($opsRate !== null) {
            $this->settingsModel->setSetting('studio_ops_hourly_rate', (float)$opsRate);
        }

        $commissionPct = $this->request->getPost('studio_commission_pct');
        if ($commissionPct !== null) {
            $this->settingsModel->setSetting('studio_commission_pct', (float)$commissionPct);
        }

        $defaultArtistRate = $this->request->getPost('default_artist_rate');
        if ($defaultArtistRate !== null) {
            $this->settingsModel->setSetting('default_artist_rate', (float)$defaultArtistRate);
        }

        return redirect()->back()->with('message', 'Studio settings updated successfully.');
    }
}
