<?php
namespace App\Controllers;

class Migrate extends BaseController
{
    public function index()
    {
        $migrate = \Config\Services::migrations();
        try {
            $migrate->latest();
            return "Migrations completed successfully.";
        } catch (\Throwable $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
