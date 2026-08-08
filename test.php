<?php
$db = new PDO('mysql:host=localhost;dbname=ensoflow_db', 'root', '');
$hash = password_hash('password', PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@enso8.com'");
$stmt->execute([$hash]);
echo "Password updated successfully.";

