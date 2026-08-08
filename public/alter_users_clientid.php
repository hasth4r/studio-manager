<?php
// Use CI4 bootstrap to get the db connection, then run the ALTER
chdir(dirname(__DIR__));

$dbPath = dirname(__DIR__) . '/writable/database.db';
if (!file_exists($dbPath)) {
    die("Database not found at $dbPath");
}

$db = new SQLite3($dbPath);

$cols = [];
$res = $db->query("PRAGMA table_info(users)");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $cols[] = $row['name'];
}
echo "Columns: " . implode(', ', $cols) . "<br>";

if (!in_array('client_id', $cols)) {
    $db->exec("ALTER TABLE users ADD COLUMN client_id INTEGER DEFAULT NULL");
    echo "<b>Added: client_id</b><br>";
} else {
    echo "client_id already exists (skipped)<br>";
}
$db->close();
echo "Done.";
