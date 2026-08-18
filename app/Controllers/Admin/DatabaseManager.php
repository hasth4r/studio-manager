<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DatabaseManager extends BaseController
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = WRITEPATH . 'backups/';
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0777, true);
        }
    }

    public function index()
    {
        if (!has_any_role(['site_manager', 'admin', 'it'])) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        helper('number');

        $backups = [];
        if (is_dir($this->backupPath)) {
            $files = scandir($this->backupPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $filePath = $this->backupPath . $file;
                    $backups[] = [
                        'name' => $file,
                        'size' => number_to_size(filesize($filePath)),
                        'date' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'timestamp' => filemtime($filePath)
                    ];
                }
            }
        }

        usort($backups, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        $data = [
            'pageTitle' => 'Database Center & Migrations',
            'backups'   => $backups
        ];

        return view('admin/database/index', $data);
    }

    /**
     * 1-Click Run Database Migrations & Schema Auto-Repair from Web UI
     */
    public function runMigrations()
    {
        if (!has_any_role(['site_manager', 'admin', 'it'])) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $db = \Config\Database::connect();

        try {
            // 1. Create Core Tables If Not Exist (with clean UTF8MB4 schema)
            $db->query("CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(191) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `global_role` VARCHAR(50) NOT NULL DEFAULT 'artist',
                `roles` TEXT NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `experience_level` VARCHAR(50) NULL DEFAULT 'Mid',
                `hourly_rate` DECIMAL(10,2) NULL DEFAULT 500.00,
                `weekly_hours` INT NULL DEFAULT 40,
                `telegram_chat_id` VARCHAR(255) NULL,
                `telegram_link_code` VARCHAR(50) NULL,
                `client_id` INT UNSIGNED NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `clients` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `company_name` VARCHAR(255) NOT NULL,
                `contact_person` VARCHAR(255) NULL,
                `email` VARCHAR(191) NULL,
                `phone` VARCHAR(50) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `collaborators` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `company_name` VARCHAR(255) NOT NULL,
                `contact_person` VARCHAR(255) NULL,
                `email` VARCHAR(191) NULL,
                `phone` VARCHAR(50) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `project_types` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `projects` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `project_code` VARCHAR(50) NOT NULL UNIQUE,
                `client_id` INT UNSIGNED NULL,
                `supervisor_id` INT UNSIGNED NULL,
                `collaborator_id` INT UNSIGNED NULL,
                `project_type_id` INT UNSIGNED NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `start_date` DATE NULL,
                `deadline` DATE NULL,
                `priority` VARCHAR(50) NOT NULL DEFAULT 'normal',
                `fps` INT NOT NULL DEFAULT 24,
                `agreed_budget` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `sequences` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `project_id` INT UNSIGNED NOT NULL,
                `supervisor_id` INT UNSIGNED NULL,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `shots` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `project_id` INT UNSIGNED NOT NULL,
                `sequence_id` INT UNSIGNED NULL,
                `shot_number` VARCHAR(50) NOT NULL,
                `description` TEXT NULL,
                `thumbnail_path` VARCHAR(255) NULL,
                `preview_video_path` VARCHAR(255) NULL,
                `reference_images` TEXT NULL,
                `pipeline_metadata` JSON NULL,
                `fps` INT NULL DEFAULT 24,
                `frame_count` INT NULL,
                `frame_in` INT NULL,
                `frame_out` INT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `assets` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `project_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(150) NOT NULL,
                `type` VARCHAR(50) NOT NULL DEFAULT 'model',
                `description` TEXT NULL,
                `thumbnail_path` VARCHAR(255) NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'in_progress',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `task_types` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `department` VARCHAR(100) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `tasks` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `project_id` INT UNSIGNED NOT NULL,
                `shot_id` INT UNSIGNED NULL,
                `asset_id` INT UNSIGNED NULL,
                `task_type_id` INT UNSIGNED NULL,
                `name` VARCHAR(150) NOT NULL,
                `description` TEXT NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'not_started',
                `priority` VARCHAR(50) NOT NULL DEFAULT 'normal',
                `assigned_to` INT UNSIGNED NULL,
                `estimated_hours` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
                `complexity` VARCHAR(50) NOT NULL DEFAULT 'Medium',
                `start_date` DATE NULL,
                `due_date` DATE NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `task_benchmarks` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `project_type_id` INT UNSIGNED NOT NULL,
                `task_name` VARCHAR(100) NOT NULL,
                `estimated_hours` DECIMAL(8,2) NOT NULL DEFAULT 8.00,
                `complexity` VARCHAR(50) NOT NULL DEFAULT 'Medium',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `reviews` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `project_id` INT UNSIGNED NOT NULL,
                `shot_id` INT UNSIGNED NULL,
                `task_id` INT UNSIGNED NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
                `created_by` INT UNSIGNED NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `review_files` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `review_id` INT UNSIGNED NOT NULL,
                `file_path` VARCHAR(255) NOT NULL,
                `file_type` VARCHAR(50) NOT NULL DEFAULT 'video',
                `version` INT NOT NULL DEFAULT 1,
                `created_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `review_comments` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `review_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NULL,
                `comment` TEXT NOT NULL,
                `frame_number` INT NULL,
                `annotation_data` JSON NULL,
                `created_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `public_share_tokens` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `token` VARCHAR(64) NOT NULL UNIQUE,
                `resource_type` VARCHAR(50) NOT NULL,
                `resource_id` INT UNSIGNED NOT NULL,
                `expires_at` DATETIME NULL,
                `created_by` INT UNSIGNED NULL,
                `created_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->query("CREATE TABLE IF NOT EXISTS `notifications` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `type` VARCHAR(50) NOT NULL DEFAULT 'info',
                `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // 2. Safely Add Missing Columns to Existing Tables
            $safeColumns = [
                ['users', 'roles', "TEXT NULL AFTER `global_role`"],
                ['users', 'hourly_rate', "DECIMAL(10,2) NULL DEFAULT 500.00 AFTER `experience_level`"],
                ['users', 'experience_level', "VARCHAR(50) NULL DEFAULT 'Mid' AFTER `status`"],
                ['users', 'weekly_hours', "INT NULL DEFAULT 40"],
                ['users', 'telegram_chat_id', "VARCHAR(255) NULL"],
                ['users', 'telegram_link_code', "VARCHAR(50) NULL"],
                ['users', 'client_id', "INT UNSIGNED NULL"],
                ['projects', 'supervisor_id', "INT UNSIGNED NULL AFTER `client_id`"],
                ['projects', 'agreed_budget', "DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `priority`"],
                ['projects', 'fps', "INT NOT NULL DEFAULT 24 AFTER `priority`"],
                ['sequences', 'supervisor_id', "INT UNSIGNED NULL AFTER `project_id`"],
                ['shots', 'preview_video_path', "VARCHAR(255) NULL AFTER `thumbnail_path`"],
                ['shots', 'reference_images', "TEXT NULL AFTER `preview_video_path`"],
                ['shots', 'pipeline_metadata', "JSON NULL AFTER `reference_images`"],
                ['shots', 'fps', "INT NULL DEFAULT 24"],
                ['shots', 'frame_count', "INT NULL"],
                ['shots', 'frame_in', "INT NULL"],
                ['shots', 'frame_out', "INT NULL"],
                ['tasks', 'complexity', "VARCHAR(50) NOT NULL DEFAULT 'Medium' AFTER `estimated_hours`"],
            ];

            foreach ($safeColumns as $col) {
                list($tableName, $colName, $colDef) = $col;
                try {
                    $check = $db->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$colName}'")->getRow();
                    if (!$check) {
                        $db->query("ALTER TABLE `{$tableName}` ADD COLUMN `{$colName}` {$colDef}");
                    }
                } catch (\Throwable $ignored) {}
            }

            // 3. Seed Default Roles for any users with empty roles
            try {
                $db->query("UPDATE `users` SET `roles` = JSON_ARRAY(`global_role`) WHERE `roles` IS NULL OR `roles` = ''");
            } catch (\Throwable $ignored) {}

            return redirect()->to('/admin/database')->with('message', 'Database schema synced & updated successfully! All tables and multi-role columns are ready.');
        } catch (\Throwable $e) {
            return redirect()->to('/admin/database')->with('error', 'Schema update error: ' . $e->getMessage());
        }
    }

    public function createBackup()
    {
        if (!has_any_role(['site_manager', 'admin', 'it'])) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $dbGroup = \Config\Database::connect()->getDatabase();
        $username = getenv('database.default.username') ?: 'root';
        $password = getenv('database.default.password');
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $this->backupPath . $filename;

        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump'; 
        }

        $passArg = !empty($password) ? "-p\"$password\"" : "";
        $command = "\"$mysqldumpPath\" -u $username $passArg $dbGroup > \"$filePath\" 2>&1";

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return redirect()->to('/admin/database')->with('message', 'Backup created successfully: ' . $filename);
        } else {
            return redirect()->to('/admin/database')->with('error', 'Backup failed. Error: ' . implode("\n", $output));
        }
    }

    public function restoreBackup()
    {
        if (!has_any_role(['site_manager', 'admin', 'it'])) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $filename = $this->request->getPost('filename');
        if (empty($filename)) {
            return redirect()->to('/admin/database')->with('error', 'No backup file selected.');
        }

        $filePath = $this->backupPath . $filename;
        if (!file_exists($filePath)) {
            return redirect()->to('/admin/database')->with('error', 'Backup file not found.');
        }

        $dbGroup = \Config\Database::connect()->getDatabase();
        $username = getenv('database.default.username') ?: 'root';
        $password = getenv('database.default.password');

        $mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
        if (!file_exists($mysqlPath)) {
            $mysqlPath = 'mysql'; 
        }

        $passArg = !empty($password) ? "-p\"$password\"" : "";
        $command = "\"$mysqlPath\" -u $username $passArg $dbGroup < \"$filePath\" 2>&1";

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return redirect()->to('/admin/database')->with('message', 'Database restored successfully from ' . $filename);
        } else {
            return redirect()->to('/admin/database')->with('error', 'Restore failed. Error: ' . implode("\n", $output));
        }
    }

    public function deleteBackup()
    {
        if (!has_any_role(['site_manager', 'admin', 'it'])) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $filename = $this->request->getPost('filename');
        if (empty($filename)) {
            return redirect()->to('/admin/database')->with('error', 'No backup file selected.');
        }

        $filePath = $this->backupPath . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
            return redirect()->to('/admin/database')->with('message', 'Backup deleted successfully.');
        }

        return redirect()->to('/admin/database')->with('error', 'Backup file not found.');
    }

    public function downloadBackup()
    {
        if (!has_any_role(['site_manager', 'admin', 'it'])) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $filename = $this->request->getGet('filename');
        if (empty($filename)) {
            return redirect()->to('/admin/database')->with('error', 'No backup file selected.');
        }

        $filePath = $this->backupPath . $filename;
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null);
        }

        return redirect()->to('/admin/database')->with('error', 'Backup file not found.');
    }
}
