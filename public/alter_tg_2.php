<?php
$db = \Config\Database::connect();
try {
    $db->query("ALTER TABLE users ADD COLUMN telegram_chat_id VARCHAR(255) NULL");
    echo "Added telegram_chat_id<br>";
} catch (\Exception $e) {
    echo $e->getMessage() . "<br>";
}

try {
    $db->query("ALTER TABLE users ADD COLUMN telegram_link_code VARCHAR(100) NULL");
    echo "Added telegram_link_code<br>";
} catch (\Exception $e) {
    echo $e->getMessage() . "<br>";
}

echo "Done.";
