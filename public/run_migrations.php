<?php
// Load CodeIgniter environment and run migrations
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__ . '/../');

require 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$migrate = \Config\Services::migrations();

try {
    $migrate->latest();
    echo "Migrations run successfully.";
} catch (\Throwable $e) {
    echo "Migration Error: " . $e->getMessage();
}
