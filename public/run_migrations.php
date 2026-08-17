<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>EnsoFlow Migration & Column Updater</h2>";

// 1. Check SQLite database file if present
$sqlitePath = dirname(__DIR__) . '/writable/database.db';
if (file_exists($sqlitePath)) {
    try {
        $pdoSqlite = new PDO('sqlite:' . $sqlitePath);
        $pdoSqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $cols = [];
        $stmt = $pdoSqlite->query("PRAGMA table_info(shots)");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cols[] = $row['name'];
        }

        $needed = [
            'preview_video_path' => 'VARCHAR(255) NULL',
            'comp_name'          => 'VARCHAR(150) NULL',
            'frame_in'           => 'INTEGER NULL',
            'frame_out'          => 'INTEGER NULL',
            'duration_seconds'   => 'NUMERIC NULL',
            'timecode_in'        => 'VARCHAR(30) NULL',
            'timecode_out'       => 'VARCHAR(30) NULL',
            'width'              => 'INTEGER NULL',
            'height'             => 'INTEGER NULL',
        ];

        foreach ($needed as $col => $type) {
            if (!in_array($col, $cols)) {
                $pdoSqlite->exec("ALTER TABLE shots ADD COLUMN {$col} {$type}");
                echo "<p style='color:green;'>&#x2705; SQLite (shots): Added <b>{$col}</b></p>";
            }
        }
        echo "<p style='color:green;'>&#x2705; SQLite Database verified and updated.</p>";
    } catch (\Throwable $e) {
        echo "<p style='color:orange;'>SQLite Note: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// 2. Parse .env for MySQL credentials
$envPath = dirname(__DIR__) . '/.env';
$dbHost = 'localhost';
$dbName = 'enso8_manager';
$dbUser = 'root';
$dbPass = '';
$dbPrefix = '';
$dbPort = 3306;

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B'\"");
            if ($key === 'database.default.hostname') $dbHost = $val;
            if ($key === 'database.default.database') $dbName = $val;
            if ($key === 'database.default.username') $dbUser = $val;
            if ($key === 'database.default.password') $dbPass = $val;
            if ($key === 'database.default.DBPrefix') $dbPrefix = $val;
            if ($key === 'database.default.port') $dbPort = (int)$val;
        }
    }
}

// 3. Connect to MySQL & Add Columns
try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdoMy = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Check table name with prefix
    $tableCandidates = [$dbPrefix . 'shots', 'shots', 'enso8_shots'];
    $targetTable = null;
    foreach ($tableCandidates as $tc) {
        try {
            $pdoMy->query("SELECT 1 FROM `{$tc}` LIMIT 1");
            $targetTable = $tc;
            break;
        } catch (\Throwable $t) {}
    }

    if ($targetTable) {
        $existingCols = [];
        $stmt = $pdoMy->query("SHOW COLUMNS FROM `{$targetTable}`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingCols[] = $row['Field'];
        }

        $myColsNeeded = [
            'preview_video_path' => "VARCHAR(255) NULL AFTER `thumbnail_path`",
            'comp_name'          => "VARCHAR(150) NULL AFTER `shot_number`",
            'frame_in'           => "INT NULL AFTER `frame_count`",
            'frame_out'          => "INT NULL AFTER `frame_in`",
            'duration_seconds'   => "DECIMAL(8,2) NULL AFTER `frame_out`",
            'timecode_in'        => "VARCHAR(30) NULL AFTER `duration_seconds`",
            'timecode_out'       => "VARCHAR(30) NULL AFTER `timecode_in`",
            'width'              => "INT NULL AFTER `timecode_out`",
            'height'             => "INT NULL AFTER `width`",
        ];

        $added = [];
        foreach ($myColsNeeded as $col => $def) {
            if (!in_array($col, $existingCols)) {
                $pdoMy->exec("ALTER TABLE `{$targetTable}` ADD COLUMN `{$col}` {$def}");
                $added[] = $col;
            }
        }

        if (!empty($added)) {
            echo "<p style='color:green;'>&#x2705; MySQL (`{$targetTable}`): Added columns <b>" . implode(', ', $added) . "</b></p>";
        } else {
            echo "<p style='color:green;'>&#x2705; MySQL (`{$targetTable}`): All pipeline columns already exist.</p>";
        }
    } else {
        echo "<p style='color:orange;'>&#x26A0; Shots table not found under prefixes in MySQL database `{$dbName}`.</p>";
    }
} catch (\Throwable $e) {
    echo "<p style='color:orange;'>MySQL Note: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p style='color:blue;'><b>&#x1F680; Database update complete! You can now import shots on work.studioinphenix.com</b></p>";
