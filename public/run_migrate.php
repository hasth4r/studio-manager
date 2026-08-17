<?php
/**
 * ONE-TIME NATIVE DATABASE UPDATER
 * Runs database column checks & migrations directly via PHP PDO (no CLI required).
 * URL: https://work.studioinphenix.com/run_migrate.php?key=enso8migrate2026
 */

$secret = $_GET['key'] ?? '';
if ($secret !== 'enso8migrate2026') {
    http_response_code(403);
    die("403 Forbidden. Add ?key=enso8migrate2026 to the URL.\n");
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== Native Database Schema Update ===\n\n";

// Load .env or CI Config to get database credentials
$envFile = dirname(__DIR__) . '/.env';
$dbConfig = [
    'hostname' => 'localhost',
    'database' => 'enso8_manager',
    'username' => 'root',
    'password' => '',
    'port'     => 3306,
];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, 'database.default.hostname') === 0) {
            $dbConfig['hostname'] = trim(explode('=', $line, 2)[1] ?? 'localhost', " '\"");
        }
        if (strpos($line, 'database.default.database') === 0) {
            $dbConfig['database'] = trim(explode('=', $line, 2)[1] ?? 'enso8_manager', " '\"");
        }
        if (strpos($line, 'database.default.username') === 0) {
            $dbConfig['username'] = trim(explode('=', $line, 2)[1] ?? 'root', " '\"");
        }
        if (strpos($line, 'database.default.password') === 0) {
            $dbConfig['password'] = trim(explode('=', $line, 2)[1] ?? '', " '\"");
        }
        if (strpos($line, 'database.default.port') === 0) {
            $dbConfig['port'] = (int)trim(explode('=', $line, 2)[1] ?? 3306, " '\"");
        }
    }
}

try {
    $dsn = "mysql:host={$dbConfig['hostname']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[✓] Connected to MySQL database: {$dbConfig['database']}\n";

    // 1. Check & Add 'reference_images' to 'shots'
    $stmt = $pdo->query("SHOW COLUMNS FROM `shots` LIKE 'reference_images'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `shots` ADD COLUMN `reference_images` LONGTEXT NULL AFTER `description`");
        echo "[+] Added column `reference_images` (LONGTEXT) to table `shots`\n";
    } else {
        echo "[i] Column `reference_images` already exists in table `shots`\n";
    }

    // 2. Check & Add 'client_notes' to 'shots'
    $stmt = $pdo->query("SHOW COLUMNS FROM `shots` LIKE 'client_notes'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `shots` ADD COLUMN `client_notes` LONGTEXT NULL AFTER `reference_images`");
        echo "[+] Added column `client_notes` (LONGTEXT) to table `shots`\n";
    } else {
        echo "[i] Column `client_notes` already exists in table `shots`\n";
    }

    // 3. Ensure uploads/references directory exists
    $targetDir = dirname(__DIR__) . '/public/uploads/references';
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
        echo "[+] Created upload directory: public/uploads/references\n";
    } else {
        echo "[i] Upload directory exists: public/uploads/references\n";
    }

    echo "\n=== Database Update Completed Successfully! ===\n";

} catch (\Throwable $e) {
    echo "\n[!] Database Error: " . $e->getMessage() . "\n";
}

// Self-delete
$selfDelete = @unlink(__FILE__);
echo $selfDelete
    ? "\n[✓] This script has self-deleted.\n"
    : "\n[!] Please delete public/run_migrate.php manually.\n";
