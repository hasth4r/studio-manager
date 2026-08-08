<?php
$db = new SQLite3('writable/database.db');
$res = $db->query('SELECT * FROM task_benchmarks;');
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
}
echo "Done.\n";
