<?php
// CodeIgniter 4.5+ Migration Runner
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();

// Load modern CI 4.5+ bootloader
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootTest($paths);

$db = \Config\Database::connect();
$prefix = $db->getPrefix();
$tableName = $prefix . 'shots';

echo "<h2>EnsoFlow Migration Runner</h2>";

// 1. Run CI Migrations
try {
    $migrate = \Config\Services::migrations();
    $migrate->latest();
    echo "<p style='color: green;'>&#x2705; CodeIgniter migrations executed successfully.</p>";
} catch (\Throwable $e) {
    echo "<p style='color: orange;'>&#x26A0; CI Migration engine message: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 2. Direct Column Verification & Auto-Add (Fail-safe)
$columnsNeeded = [
    'comp_name'        => "VARCHAR(150) NULL AFTER `shot_number`",
    'frame_in'         => "INT NULL AFTER `frame_count`",
    'frame_out'        => "INT NULL AFTER `frame_in`",
    'duration_seconds' => "DECIMAL(8,2) NULL AFTER `frame_out`",
    'timecode_in'      => "VARCHAR(30) NULL AFTER `duration_seconds`",
    'timecode_out'     => "VARCHAR(30) NULL AFTER `timecode_in`",
    'width'            => "INT NULL AFTER `timecode_out`",
    'height'           => "INT NULL AFTER `width`",
];

$existingCols = [];
$res = $db->query("SHOW COLUMNS FROM `{$tableName}`");
if ($res) {
    foreach ($res->getResultArray() as $row) {
        $existingCols[] = $row['Field'];
    }
}

$added = [];
foreach ($columnsNeeded as $col => $def) {
    if (!in_array($col, $existingCols)) {
        $db->query("ALTER TABLE `{$tableName}` ADD COLUMN `{$col}` {$def}");
        $added[] = $col;
    }
}

if (!empty($added)) {
    echo "<p style='color: green;'>&#x2705; Added missing columns to `{$tableName}`: <b>" . implode(', ', $added) . "</b></p>";
} else {
    echo "<p style='color: green;'>&#x2705; All pipeline columns already exist in `{$tableName}`.</p>";
}

echo "<hr><p><b>Database is up to date and ready for AE / CSV imports!</b></p>";
