<?php
$dbSqlite = new PDO('sqlite:writable/database.db');
echo "SQLITE USERS:\n";
print_r($dbSqlite->query('SELECT id, name, email, global_role FROM users')->fetchAll(PDO::FETCH_ASSOC));

$mysqlHost = 'localhost';
$mysqlDb = 'enso8_manager';
$mysqlUser = 'root';
$mysqlPass = '';
$pdoMysql = new PDO("mysql:host=$mysqlHost;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass);
echo "MYSQL USERS:\n";
print_r($pdoMysql->query('SELECT id, name, email, global_role FROM enso8_users')->fetchAll(PDO::FETCH_ASSOC));
