<?php
/**
 * ONE-TIME NATIVE DATABASE SCHEMA MIGRATOR & DIAGNOSTIC
 * Discovers all tables, checks prefix, and adds reference_images & client_notes columns.
 * URL: https://work.studioinphenix.com/run_migrate.php?key=enso8migrate2026
 */

$secret = $_GET['key'] ?? '';
if ($secret !== 'enso8migrate2026') {
    http_response_code(403);
    die("403 Forbidden. Add ?key=enso8migrate2026 to the URL.\n");
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== Native Database Schema Discovery & Update ===\n\n";

// Load .env credentials
$envFile = dirname(__DIR__) . '/.env';
$dbConfig = [
    'hostname' => 'localhost',
    'database' => '',
    'username' => 'root',
    'password' => '',
    'port'     => 3306,
    'prefix'   => '',
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
            $dbConfig['database'] = trim(explode('=', $line, 2)[1] ?? '', " '\"");
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
        if (strpos($line, 'database.default.DBPrefix') === 0) {
            $dbConfig['prefix'] = trim(explode('=', $line, 2)[1] ?? '', " '\"");
        }
    }
}

try {
    $dsn = "mysql:host={$dbConfig['hostname']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[✓] Connected to MySQL database: {$dbConfig['database']}\n\n";

    // 1. List all tables in this database
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables in database:\n";
    foreach ($tables as $t) {
        echo "  - {$t}\n";
    }
    echo "\n";

    // 2. Identify the shots table (direct, with prefix, or matching *shot*)
    $shotsTable = null;
    if (in_array('shots', $tables)) {
        $shotsTable = 'shots';
    } elseif (!empty($dbConfig['prefix']) && in_array($dbConfig['prefix'] . 'shots', $tables)) {
        $shotsTable = $dbConfig['prefix'] . 'shots';
    } else {
        foreach ($tables as $t) {
            if (strpos($t, 'shot') !== false) {
                $shotsTable = $t;
                break;
            }
        }
    }

    if ($shotsTable) {
        echo "[✓] Identified shots table: `{$shotsTable}`\n";

        // Check & Add 'reference_images'
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$shotsTable}` LIKE 'reference_images'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `{$shotsTable}` ADD COLUMN `reference_images` LONGTEXT NULL");
            echo "[+] Added column `reference_images` (LONGTEXT) to table `{$shotsTable}`\n";
        } else {
            echo "[i] Column `reference_images` already exists in `{$shotsTable}`\n";
        }

        // Check & Add 'client_notes'
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$shotsTable}` LIKE 'client_notes'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `{$shotsTable}` ADD COLUMN `client_notes` LONGTEXT NULL");
            echo "[+] Added column `client_notes` (LONGTEXT) to table `{$shotsTable}`\n";
        } else {
            echo "[i] Column `client_notes` already exists in `{$shotsTable}`\n";
        }
    } else {
        echo "[!] No table matching 'shots' was found in `{$dbConfig['database']}`.\n";
    }

    // 3. Ensure uploads/references directory exists
    $targetDir = dirname(__DIR__) . '/public/uploads/references';
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
        echo "[+] Created upload directory: public/uploads/references\n";
    } else {
        echo "[i] Upload directory exists: public/uploads/references\n";
    }

    echo "\n=== Script execution finished. ===\n";

} catch (\Throwable $e) {
    echo "\n[!] Database Error: " . $e->getMessage() . "\n";
}
