<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<style>
    body { font-family: monospace; background: #0f0f0f; color: #eee; padding: 20px; }
    h2 { color: #3ea6ff; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #333; padding: 8px 12px; text-align: left; }
    th { background: #1a1a1a; color: #aaa; }
    .badge-yes { color: #4ade80; font-weight: bold; }
    .badge-no { color: #f87171; font-weight: bold; }
    video { max-width: 260px; border-radius: 6px; background: #000; }
</style>";

echo "<h2>&#x1F3AC; EnsoFlow Media &amp; Video Diagnostic Tool</h2>";

// 1. Check Files on Server Disk
$videoDir = __DIR__ . '/uploads/shots/videos';
echo "<h3>1. Files on Server Disk: <code>public/uploads/shots/videos</code></h3>";
if (!is_dir($videoDir)) {
    echo "<p class='badge-no'>&#x274C; Directory not found: $videoDir</p>";
} else {
    $files = scandir($videoDir);
    $videoFiles = array_filter($files, fn($f) => !in_array($f, ['.', '..']));
    echo "<p>Total files on disk: <b>" . count($videoFiles) . "</b></p>";
    if (!empty($videoFiles)) {
        echo "<ul>";
        foreach ($videoFiles as $vf) {
            $fsize = round(filesize($videoDir . '/' . $vf) / (1024 * 1024), 2);
            echo "<li><b>$vf</b> ($fsize MB) &mdash; <a href='/uploads/shots/videos/$vf' target='_blank' style='color:#3ea6ff;'>Direct Link</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='badge-no'>No video files found in uploads/shots/videos yet.</p>";
    }
}

// 2. Check Database Shots Table
echo "<h3>2. Database Status (Shots with Video Previews)</h3>";

$sqlitePath = dirname(__DIR__) . '/writable/database.db';
$envPath = dirname(__DIR__) . '/.env';

$pdo = null;
if (file_exists($sqlitePath)) {
    try {
        $pdo = new PDO('sqlite:' . $sqlitePath);
    } catch (\Throwable $e) {}
}

if (!$pdo && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $dbHost = 'localhost'; $dbName = 'enso8_manager'; $dbUser = 'root'; $dbPass = ''; $dbPort = 3306;
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
            if ($key === 'database.default.port') $dbPort = (int)$val;
        }
    }
    try {
        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
    } catch (\Throwable $e) {}
}

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, project_id, shot_number, comp_name, preview_video_path, thumbnail_path FROM shots ORDER BY id ASC LIMIT 50");
        $shots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table>";
        echo "<tr><th>Shot ID</th><th>Shot Number</th><th>Comp Name</th><th>Preview Video Path</th><th>File Exists on Disk?</th><th>Live Video Player</th></tr>";
        
        $hasAnyVideo = false;
        foreach ($shots as $s) {
            $vpath = $s['preview_video_path'] ?? null;
            $diskExists = false;
            if ($vpath) {
                $hasAnyVideo = true;
                $fullPath = __DIR__ . '/' . ltrim($vpath, '/');
                $diskExists = file_exists($fullPath);
            }

            echo "<tr>";
            echo "<td>{$s['id']}</td>";
            echo "<td><b>{$s['shot_number']}</b></td>";
            echo "<td>" . ($s['comp_name'] ?: '-') . "</td>";
            echo "<td>" . ($vpath ? "<code>$vpath</code>" : "<span class='badge-no'>None</span>") . "</td>";
            echo "<td>" . ($vpath ? ($diskExists ? "<span class='badge-yes'>&#x2705; YES</span>" : "<span class='badge-no'>&#x274C; Missing file</span>") : "-") . "</td>";
            echo "<td>";
            if ($vpath && $diskExists) {
                echo "<video src='/$vpath' controls></video>";
            } else {
                echo "<span style='color:#666;'>No video</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";

        if (!$hasAnyVideo) {
            echo "<p class='badge-no'>&#x26A0; No shots in the database have a preview_video_path assigned yet.</p>";
        }
    } catch (\Throwable $e) {
        echo "<p class='badge-no'>DB Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='badge-no'>Could not connect to database to inspect shots.</p>";
}
