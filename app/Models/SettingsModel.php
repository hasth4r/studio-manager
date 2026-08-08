<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['setting_key', 'setting_value'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get a setting by key
     */
    public function getSetting($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set or update a setting by key
     */
    public function setSetting($key, $value)
    {
        $setting = $this->where('setting_key', $key)->first();
        if ($setting) {
            $this->update($setting->id, ['setting_value' => $value]);
        } else {
            $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value
            ]);
        }
    }
}
