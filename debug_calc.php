<?php
// Quick diagnostic - remove after debugging
require_once 'vendor/autoload.php';

$db = new PDO('sqlite:' . __DIR__ . '/writable/database/eso8.db');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "<style>body{font-family:monospace;background:#111;color:#eee;padding:20px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #444;padding:6px 10px;text-align:left}th{background:#222}tr:hover{background:#1a1a1a}.ok{color:#4f4},.err{color:#f44}.warn{color:#fa4}h2{color:#3da2ff;margin-top:30px}</style>";

echo "<h1>🔍 Calculation Diagnostic</h1>";

// 1. Projects + FPS
echo "<h2>1. Projects & FPS</h2><table><tr><th>ID</th><th>Name</th><th>FPS</th></tr>";
foreach ($db->query("SELECT id, name, fps FROM projects") as $r) {
    $fpsBad = empty($r['fps']) ? "class='err'" : "class='ok'";
    echo "<tr><td>{$r['id']}</td><td>{$r['name']}</td><td {$fpsBad}>{$r['fps']}</td></tr>";
}
echo "</table>";

// 2. Shots + frames
echo "<h2>2. Shots - Frame Count & FPS</h2><table><tr><th>ID</th><th>Shot #</th><th>Project ID</th><th>frame_count</th><th>fps</th></tr>";
foreach ($db->query("SELECT id, shot_number, project_id, frame_count, fps FROM shots LIMIT 20") as $r) {
    $fcBad = empty($r['frame_count']) ? "style='color:#fa4'" : "style='color:#4f4'";
    echo "<tr><td>{$r['id']}</td><td>{$r['shot_number']}</td><td>{$r['project_id']}</td><td {$fcBad}>{$r['frame_count']}</td><td>{$r['fps']}</td></tr>";
}
echo "</table>";

// 3. Benchmarks
echo "<h2>3. Task Benchmarks</h2><table><tr><th>Project ID</th><th>Task Type ID</th><th>Simple</th><th>Medium</th><th>Complex</th></tr>";
$bms = $db->query("SELECT * FROM task_benchmarks")->fetchAll();
if (empty($bms)) {
    echo "<tr><td colspan='5' style='color:#f44'>⚠️ NO BENCHMARKS SET! Go to Project > Benchmarks tab and save values.</td></tr>";
} else {
    foreach ($bms as $r) {
        $allZero = ($r['simple_hours'] == 0 && $r['medium_hours'] == 0 && $r['complex_hours'] == 0);
        $style = $allZero ? "style='color:#fa4'" : "style='color:#4f4'";
        echo "<tr {$style}><td>{$r['project_id']}</td><td>{$r['task_type_id']}</td><td>{$r['simple_hours']}</td><td>{$r['medium_hours']}</td><td>{$r['complex_hours']}</td></tr>";
    }
}
echo "</table>";

// 4. Tasks with estimation status
echo "<h2>4. Tasks (estimation status)</h2><table><tr><th>ID</th><th>Project</th><th>Shot</th><th>Task Type</th><th>Complexity</th><th>frame_count</th><th>fps</th><th>estimated_hours</th><th>Assignee</th></tr>";
foreach ($db->query("SELECT t.id, t.project_id, t.shot_id, t.task_type_id, t.complexity, t.frame_count, t.fps, t.estimated_hours, t.assigned_to FROM tasks t LIMIT 30") as $r) {
    $estBad = empty($r['estimated_hours']) ? "style='color:#f44'" : "style='color:#4f4'";
    echo "<tr><td>{$r['id']}</td><td>{$r['project_id']}</td><td>{$r['shot_id']}</td><td>{$r['task_type_id']}</td><td>{$r['complexity']}</td><td>{$r['frame_count']}</td><td>{$r['fps']}</td><td {$estBad}>{$r['estimated_hours']}</td><td>{$r['assigned_to']}</td></tr>";
}
echo "</table>";

// 5. Simulation of one task's calculation
echo "<h2>5. Simulation - What would task calc give?</h2>";
$task = $db->query("SELECT t.*, s.frame_count as shot_fc, s.fps as shot_fps, p.fps as proj_fps FROM tasks t LEFT JOIN shots s ON s.id = t.shot_id LEFT JOIN projects p ON p.id = t.project_id WHERE t.shot_id IS NOT NULL LIMIT 1")->fetch();
if ($task) {
    $bm = $db->query("SELECT * FROM task_benchmarks WHERE project_id={$task['project_id']} AND task_type_id={$task['task_type_id']}")->fetch();
    $complexity = $task['complexity'] ?: 'Medium';
    $baseHours = 0;
    if ($bm) {
        if ($complexity === 'Simple')  $baseHours = $bm['simple_hours'];
        if ($complexity === 'Medium')  $baseHours = $bm['medium_hours'];
        if ($complexity === 'Complex') $baseHours = $bm['complex_hours'];
    }
    $finalFps = $task['fps'] ?: ($task['shot_fps'] ?: ($task['proj_fps'] ?: 24));
    $finalFrameCount = $task['frame_count'] ?: $task['shot_fc'];
    $durationMult = ($finalFrameCount && $finalFps > 0) ? ($finalFrameCount / $finalFps) : 0;
    $estimated = $baseHours * $durationMult;

    echo "<pre>";
    echo "Task ID: {$task['id']}\n";
    echo "Complexity: {$complexity}\n";
    echo "Benchmark found: " . ($bm ? 'YES' : '❌ NO - THIS IS THE PROBLEM') . "\n";
    if ($bm) echo "Base Hours ({$complexity}): {$baseHours}" . ($baseHours == 0 ? " ❌ ZERO - SET NON-ZERO BENCHMARKS" : " ✅") . "\n";
    echo "Task frame_count: {$task['frame_count']}, Shot frame_count: {$task['shot_fc']}\n";
    echo "Final frame_count used: {$finalFrameCount}" . (empty($finalFrameCount) ? " ❌ NO FRAMES SET" : " ✅") . "\n";
    echo "Task fps: {$task['fps']}, Shot fps: {$task['shot_fps']}, Project fps: {$task['proj_fps']}\n";
    echo "Final FPS used: {$finalFps} ✅\n";
    echo "Duration multiplier (frames/fps): {$durationMult}\n";
    echo "Estimated Hours = {$baseHours} * {$durationMult} = " . round($estimated, 2) . "\n";
    echo "</pre>";
} else {
    echo "<p>No shot tasks found.</p>";
}
