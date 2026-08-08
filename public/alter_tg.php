<?php
$path = '../writable/database.db';
$pdo = new PDO('sqlite:' . $path);

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN telegram_chat_id VARCHAR(255) NULL");
    echo "Added telegram_chat_id<br>";
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN telegram_link_code VARCHAR(100) NULL");
    echo "Added telegram_link_code<br>";
} catch (Exception $e) {}

echo "Done.";
