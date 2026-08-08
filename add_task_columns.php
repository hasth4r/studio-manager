<?php
$db = new SQLite3('C:/xampp/htdocs/eso8manager_v0.0.1/writable/database.db');
$db->exec('ALTER TABLE tasks ADD COLUMN due_date DATE NULL');
$db->exec('ALTER TABLE tasks ADD COLUMN estimated_hours REAL NULL');
$db->exec('ALTER TABLE tasks ADD COLUMN notes TEXT NULL');
echo 'Columns added successfully.';
