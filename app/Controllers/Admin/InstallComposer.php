<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class InstallComposer extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || !has_any_role(['site_manager', 'admin', 'it'])) {
            return redirect()->to('/login');
        }

        echo "<h1>Installing AWS SDK via Composer...</h1>";
        echo "<pre style='background: #111; color: #0f0; padding: 20px; border-radius: 5px;'>";
        flush();
        
        // Put the environment variables so Composer runs correctly
        putenv('COMPOSER_HOME=' . WRITEPATH . 'composer_home');
        
        $output = null;
        $resultCode = null;
        
        // Change to the root directory where composer.phar and composer.json live
        $rootPath = FCPATH . '..';
        
        // Run composer install using the local phar file with absolute paths
        exec("cd {$rootPath} && php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs 2>&1", $output, $resultCode);

        foreach ($output as $line) {
            echo htmlspecialchars($line) . "\n";
            flush();
        }

        echo "\n----------------------------------------\n";
        if ($resultCode === 0) {
            echo "SUCCESS! The AWS SDK is installed.\n";
        } else {
            echo "FAILED! Error code: $resultCode\n";
        }
        echo "</pre>";
        echo "<a href='/admin/dashboard'>Return to Dashboard</a>";
    }
}
