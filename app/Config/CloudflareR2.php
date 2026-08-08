<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CloudflareR2 extends BaseConfig
{
    public string $key      = '';
    public string $secret   = '';
    public string $bucket   = '';
    public string $endpoint = '';
    public string $region   = 'auto';

    public function __construct()
    {
        parent::__construct();

        // Load from .env
        $this->key      = env('r2.key', '');
        $this->secret   = env('r2.secret', '');
        $this->bucket   = env('r2.bucket', '');
        $this->endpoint = env('r2.endpoint', '');
        $this->region   = env('r2.region', 'auto');
    }
}
