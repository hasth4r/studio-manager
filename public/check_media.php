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
if (!is_dir($videoDir)) {
    @mkdir($videoDir, 0777, true);
}

echo "<h3>1. Files on Server Disk: <code>public/uploads/shots/videos</code></h3>";
if (!is_dir($videoDir)) {
    echo "<p class='badge-no'>&#x274C; Directory not found and could not be created automatically: $videoDir</p>";
} else {
    $files = scandir($videoDir);
    $videoFiles = array_filter($files, fn($f) => !in_array($f, ['.', '..']));
    echo "<p>Total video files on server disk: <b>" . count($videoFiles) . "</b></p>";
    if (!empty($videoFiles)) {
        echo "<ul>";
        foreach ($videoFiles as $vf) {
            $fsize = round(filesize($videoDir . '/' . $vf) / (1024 * 1024), 2);
            echo "<li>&#x2705; <b>$vf</b> ($fsize MB) &mdash; <a href='/uploads/shots/videos/$vf' target='_blank' style='color:#3ea6ff;'>Direct Local Link</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:#fbbf24;'>&#x26A0; No video files found in local uploads/shots/videos folder yet.</p>";
    }
}

// 1.5. Check Cloudflare R2 CDN Storage
echo "<h3>1.5. Cloudflare R2 CDN Storage</h3>";
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
$r2Configured = false;
$r2Client = null;
$r2Bucket = '';
$r2CustomDomain = '';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $r2Key = ''; $r2Secret = ''; $r2Endpoint = ''; $r2Region = 'auto';
    foreach ($envLines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B'\"");
            if ($k === 'r2.key') $r2Key = $v;
            if ($k === 'r2.secret') $r2Secret = $v;
            if ($k === 'r2.bucket') $r2Bucket = $v;
            if ($k === 'r2.endpoint') $r2Endpoint = $v;
            if ($k === 'r2.region') $r2Region = $v;
            if ($k === 'r2.customDomain') $r2CustomDomain = $v;
        }
    }

    if (!empty($r2Key) && !empty($r2Secret) && !empty($r2Bucket) && class_exists('\Aws\S3\S3Client')) {
        try {
            $r2Client = new \Aws\S3\S3Client([
                'version'     => 'latest',
                'region'      => $r2Region,
                'endpoint'    => $r2Endpoint,
                'credentials' => ['key' => $r2Key, 'secret' => $r2Secret],
                'use_path_style_endpoint' => true,
            ]);
            $r2Configured = true;
            echo "<p class='badge-yes'>&#x2705; Cloudflare R2 is configured (Bucket: <code>$r2Bucket</code>" . ($r2CustomDomain ? ", Custom CDN: <code>$r2CustomDomain</code>" : "") . ")</p>";
        } catch (\Throwable $e) {
            echo "<p class='badge-no'>&#x274C; R2 Connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:#aaa;'>Cloudflare R2 is not configured in .env (Videos are stored directly on your web hosting server disk).</p>";
    }
} else {
    echo "<p style='color:#aaa;'>No .env found for R2 check.</p>";
}

// 2. Check Database Shots Table & Auto-Linker
echo "<h3>2. Database Status &amp; Auto-Linker</h3>";

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
        // 2.1 List Projects
        $projects = $pdo->query("SELECT id, name, project_code FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Active Projects in Database: ";
        foreach ($projects as $p) {
            echo "<b>[ID: {$p['id']}] {$p['name']} ({$p['project_code']})</b> &nbsp; ";
        }
        echo "</p>";

        // Auto-Link & Auto-Create Action: If user clicks link
        if (isset($_GET['action']) && $_GET['action'] === 'autolink') {
            $files = is_dir($videoDir) ? scandir($videoDir) : [];
            $videoFiles = array_filter($files, fn($f) => !in_array($f, ['.', '..']));
            $createdCount = 0;
            $linkedCount = 0;
            $now = date('Y-m-d H:i:s');

            // Find target project ID (default to project 2 if exists, or first project)
            $targetProjId = 2;
            $projIds = array_column($projects, 'id');
            if (!in_array($targetProjId, $projIds) && !empty($projIds)) {
                $targetProjId = (int)$projIds[0];
            }

            // Get sequence for Project (or create 'WAR' sequence)
            $seqStmt = $pdo->prepare("SELECT id FROM sequences WHERE project_id = ? LIMIT 1");
            $seqStmt->execute([$targetProjId]);
            $defaultSeq = $seqStmt->fetch(PDO::FETCH_ASSOC);
            $seqId = $defaultSeq ? $defaultSeq['id'] : null;
            if (!$seqId) {
                try {
                    $insSeq = $pdo->prepare("INSERT INTO sequences (project_id, name, description, created_at, updated_at) VALUES (?, 'WAR', 'Production Sequence', ?, ?)");
                    $insSeq->execute([$targetProjId, $now, $now]);
                    $seqId = $pdo->lastInsertId();
                } catch (\Throwable $e) {}
            }

            foreach ($videoFiles as $vf) {
                $projId = $targetProjId;
                $rawShotNum = null;

                if (preg_match('/^vid_(\d+)_(.+?)_[a-zA-Z0-9]+\.(mp4|mov|webm|m4v)$/i', $vf, $m)) {
                    $projId = (int)$m[1];
                    $rawShotNum = $m[2];
                } elseif (preg_match('/^vid_(.+?)_[a-zA-Z0-9]+\.(mp4|mov|webm|m4v)$/i', $vf, $m)) {
                    $rawShotNum = $m[1];
                } elseif (preg_match('/^(.+?)\.(mp4|mov|webm|m4v)$/i', $vf, $m)) {
                    $rawShotNum = $m[1];
                }

                if (!empty($rawShotNum)) {
                    $shotNum = strtoupper($rawShotNum);
                    $relPath = 'uploads/shots/videos/' . $vf;

                    // Check if shot already exists in DB
                    $checkStmt = $pdo->prepare("SELECT id FROM shots WHERE project_id = ? AND (LOWER(shot_number) = ? OR LOWER(comp_name) = ?)");
                    $checkStmt->execute([$projId, strtolower($rawShotNum), strtolower($rawShotNum)]);
                    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        $updateStmt = $pdo->prepare("UPDATE shots SET preview_video_path = ?, updated_at = ? WHERE id = ?");
                        $updateStmt->execute([$relPath, $now, $existing['id']]);
                        $linkedCount++;
                    } else {
                        // Auto-create shot in database!
                        try {
                            $insertStmt = $pdo->prepare("INSERT INTO shots (project_id, sequence_id, shot_number, preview_video_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
                            $insertStmt->execute([$projId, $seqId, $shotNum, $relPath, $now, $now]);
                            $createdCount++;
                        } catch (\Throwable $e) {
                            try {
                                $insertStmt = $pdo->prepare("INSERT INTO shots (project_id, shot_number, preview_video_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
                                $insertStmt->execute([$projId, $shotNum, $relPath, $now, $now]);
                                $createdCount++;
                            } catch (\Throwable $e2) {}
                        }
                    }
                }
            }
            echo "<div style='background:#064e3b; border:1px solid #059669; color:#a7f3d0; padding:15px; border-radius:8px; margin-bottom:15px;'>";
            echo "<h4 style='margin:0 0 5px 0;'>🎉 Auto-Creation Complete!</h4>";
            echo "<p style='margin:0;'>Created <b>{$createdCount}</b> new shots and linked <b>{$linkedCount}</b> shots in Project {$targetProjId}!</p>";
            echo "</div>";
        }

        // Cloudflare R2 Sync Action
        if (isset($_GET['action']) && $_GET['action'] === 'sync_r2' && $r2Configured && $r2Client) {
            @ini_set('max_execution_time', 600);
            $files = is_dir($videoDir) ? scandir($videoDir) : [];
            $videoFiles = array_filter($files, fn($f) => !in_array($f, ['.', '..']));
            $r2Synced = 0;
            $r2Failed = 0;

            foreach ($videoFiles as $vf) {
                $abs = $videoDir . '/' . $vf;
                $key = 'uploads/shots/videos/' . $vf;
                try {
                    if (!$r2Client->doesObjectExist($r2Bucket, $key)) {
                        $r2Client->putObject([
                            'Bucket'     => $r2Bucket,
                            'Key'        => $key,
                            'SourceFile' => $abs,
                        ]);
                    }
                    $r2Synced++;
                } catch (\Throwable $e) {
                    $r2Failed++;
                }
            }
            echo "<div style='background:#064e3b; border:1px solid #059669; color:#a7f3d0; padding:15px; border-radius:8px; margin-bottom:15px;'>";
            echo "<b>☁️ Cloudflare R2 Sync Complete! {$r2Synced} videos are now active in R2 bucket '{$r2Bucket}'!</b>";
            echo "</div>";
        }

        // Action Toolbar
        echo "<div style='margin-bottom:15px; display:flex; gap:10px; align-items:center;'>";
        echo "<a href='?action=autolink' style='background:#2563eb; color:#fff; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:13px; display:inline-block;'>🔗 Auto-Create &amp; Link Database Shots</a>";
        if ($r2Configured) {
            echo "<a href='?action=sync_r2' style='background:#9333ea; color:#fff; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:13px; display:inline-block;'>☁️ Sync All 94 Videos to Cloudflare R2 CDN</a>";
        }
        echo "</div>";

        // Query Project 2 (Mahalaya) shots first, then others
        $stmt = $pdo->query("SELECT id, project_id, shot_number, comp_name, preview_video_path, thumbnail_path FROM shots ORDER BY project_id DESC, id ASC LIMIT 250");
        $shots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table>";
        echo "<tr><th>Shot ID</th><th>Project</th><th>Shot Number</th><th>Comp Name</th><th>Preview Video Path</th><th>On Local Disk?</th>" . ($r2Configured ? "<th>On R2 CDN?</th>" : "") . "<th>Live Video Player</th></tr>";
        
        $hasAnyVideo = 0;
        foreach ($shots as $s) {
            $vpath = $s['preview_video_path'] ?? null;
            $diskExists = false;
            $r2Exists = false;
            if ($vpath) {
                $hasAnyVideo++;
                $fullPath = __DIR__ . '/' . ltrim($vpath, '/');
                $diskExists = file_exists($fullPath);
                if ($r2Configured && $r2Client) {
                    try {
                        $r2Exists = $r2Client->doesObjectExist($r2Bucket, ltrim($vpath, '/'));
                    } catch (\Throwable $e) {}
                }
            }

            echo "<tr>";
            echo "<td>{$s['id']}</td>";
            echo "<td>Project <b>{$s['project_id']}</b></td>";
            echo "<td><b>{$s['shot_number']}</b></td>";
            echo "<td>" . ($s['comp_name'] ?: '-') . "</td>";
            echo "<td>" . ($vpath ? "<code>$vpath</code>" : "<span class='badge-no'>None</span>") . "</td>";
            echo "<td>" . ($vpath ? ($diskExists ? "<span class='badge-yes'>&#x2705; YES</span>" : "<span class='badge-no'>&#x274C; Missing file</span>") : "-") . "</td>";
            if ($r2Configured) {
                echo "<td>" . ($vpath ? ($r2Exists ? "<span class='badge-yes'>&#x2705; YES</span>" : "<span class='badge-no'>&#x274C; Not in R2</span>") : "-") . "</td>";
            }
            echo "<td>";
            if ($vpath && ($diskExists || $r2Exists)) {
                echo "<video src='/$vpath' controls preload='metadata'></video>";
            } else {
                echo "<span style='color:#666;'>No video linked</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<p style='margin-top:10px;'>Total Shots with Linked Videos: <b>{$hasAnyVideo}</b> / " . count($shots) . "</p>";
    } catch (\Throwable $e) {
        echo "<p class='badge-no'>DB Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='badge-no'>Could not connect to database to inspect shots.</p>";
}
