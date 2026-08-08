<?php
$db = new SQLite3('writable/database.db');
$queries = [
    "ALTER TABLE projects ADD COLUMN fps INTEGER DEFAULT 24;",
    "ALTER TABLE shots ADD COLUMN fps INTEGER DEFAULT NULL;",
    "ALTER TABLE shots ADD COLUMN frame_count INTEGER DEFAULT NULL;",
    "ALTER TABLE tasks ADD COLUMN fps INTEGER DEFAULT NULL;",
    "ALTER TABLE tasks ADD COLUMN frame_count INTEGER DEFAULT NULL;"
];

foreach ($queries as $q) {
    try {
        $db->exec($q);
        echo "Success: $q<br>";
    } catch (Exception $e) {
        echo "Error on $q: " . $e->getMessage() . "<br>";
    }
}
echo "Done.<br>";
