<?php
$db = new SQLite3('C:/xampp/htdocs/eso8manager_v0.0.1/writable/database.db');
$db->exec("ALTER TABLE tasks ADD COLUMN gantt_row INTEGER DEFAULT 0");
echo "Done";
