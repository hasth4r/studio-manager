<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();
        try {
            $db->query("ALTER TABLE users ADD COLUMN client_id INTEGER DEFAULT NULL");
            return "Added client_id successfully!";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
