<?php
$mysqli = new mysqli("localhost", "root", "", "ensoflow_db");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$res = $mysqli->query("DESCRIBE users");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Table 'users' does not exist or error: " . $mysqli->error;
}
$mysqli->close();
?>
