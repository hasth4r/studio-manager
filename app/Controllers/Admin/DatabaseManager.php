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
        if (session()->get('userRole') !== 'admin') {
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
            'pageTitle' => 'Database Manager',
            'backups' => $backups
        ];

        return view('admin/database/index', $data);
    }

    public function createBackup()
    {
        if (session()->get('userRole') !== 'admin') {
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
        if (session()->get('userRole') !== 'admin') {
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
        if (session()->get('userRole') !== 'admin') {
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
        if (session()->get('userRole') !== 'admin') {
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
