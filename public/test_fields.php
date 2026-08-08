<?php
$db = new PDO('sqlite:../writable/database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$result = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    echo $row['name'] . "<br>";
}
