<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CloudflareStorage;

class MigrateR2 extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $userRole = session()->get('userRole');
        if (!in_array(strtolower($userRole), ['admin', 'system admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $r2 = new CloudflareStorage();
        if (!$r2->isConfigured()) {
            return "<h1>Cloudflare R2 is not configured in .env!</h1><p>Please configure R2 first.</p>";
        }

        echo "<h1>Migrating existing local videos to Cloudflare R2...</h1>";
        echo "<pre>";
        flush();

        $uploadsDir = WRITEPATH . 'uploads/';
        if (!is_dir($uploadsDir)) {
            echo "No uploads directory found. Nothing to migrate.</pre>";
            return;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadsDir));
        $migrated = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            
            $absolutePath = $file->getPathname();
            // Get relative path (e.g. 'project_x/shots/.../file.mp4')
            $relativePath = str_replace($uploadsDir, '', $absolutePath);
            $relativePath = str_replace('\\', '/', $relativePath); // Normalize slashes for Windows

            // R2 destination path
            $r2Path = 'uploads/' . $relativePath;

            // Check if file exists on R2 first
            if ($r2->fileExists($r2Path)) {
                echo "SKIPPED (Already in R2): " . $relativePath . "\n";
                // Optionally delete the local file since it's already backed up to R2
                @unlink($absolutePath);
                $skipped++;
                continue;
            }

            echo "UPLOADING: " . $relativePath . " ... ";
            flush();

            if ($r2->uploadFile($absolutePath, $r2Path)) {
                echo "SUCCESS\n";
                // Delete local file to save space
                @unlink($absolutePath);
                $migrated++;
            } else {
                echo "FAILED\n";
                $failed++;
            }
            flush();
        }

        echo "\n==========================================\n";
        echo "MIGRATION COMPLETE!\n";
        echo "Migrated: {$migrated}\n";
        echo "Skipped (Already there): {$skipped}\n";
        echo "Failed: {$failed}\n";
        echo "==========================================\n";
        echo "</pre>";
        echo "<a href='/admin/dashboard'>Return to Dashboard</a>";
    }
}
