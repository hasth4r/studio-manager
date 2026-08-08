<?php
// Diagnostic tool - uses CI4 DB connection
// Visit: /debug_calc to see results
// DELETE THIS FILE AFTER DEBUGGING

$db = \Config\Database::connect();

header('Content-Type: text/html');
echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:20px}table{border-collapse:collapse;width:100%;margin-bottom:20px}th,td{border:1px solid #444;padding:6px 10px;text-align:left}th{background:#222}.ok{color:#4f4}.err{color:#f44}.warn{color:#fa4}h2{color:#3da2ff;margin-top:30px}pre{background:#1a1a1a;padding:15px;border-radius:6px;border:1px solid #444}</style>';
echo '<h1>🔍 Calculation Diagnostic</h1>';

// 1. Projects & FPS
echo '<h2>1. Projects & FPS</h2><table><tr><th>ID</th><th>Name</th><th>FPS</th></tr>';
foreach ($db->table('projects')->select('id, name, fps')->get()->getResult() as $r) {
    $cls = empty($r->fps) ? 'class="err"' : 'class="ok"';
    echo "<tr><td>{$r->id}</td><td>{$r->name}</td><td {$cls}>" . ($r->fps ?: '❌ NOT SET') . "</td></tr>";
}
echo '</table>';

// 2. Shots
echo '<h2>2. Shots — Frame Count & FPS</h2><table><tr><th>ID</th><th>Shot#</th><th>Project</th><th>frame_count</th><th>fps</th></tr>';
foreach ($db->table('shots')->select('id, shot_number, project_id, frame_count, fps')->limit(20)->get()->getResult() as $r) {
    $cls = empty($r->frame_count) ? 'class="warn"' : 'class="ok"';
    echo "<tr><td>{$r->id}</td><td>{$r->shot_number}</td><td>{$r->project_id}</td><td {$cls}>" . ($r->frame_count ?: '⚠ Not set') . "</td><td>" . ($r->fps ?: 'uses project') . "</td></tr>";
}
echo '</table>';

// 3. Benchmarks - THE KEY CHECK
echo '<h2>3. Task Benchmarks</h2>';
$bms = $db->table('task_benchmarks')->select('task_benchmarks.*, task_types.name as type_name')->join('task_types', 'task_types.id = task_benchmarks.task_type_id', 'left')->get()->getResult();
if (empty($bms)) {
    echo '<p class="err">❌ NO BENCHMARKS EXIST IN DATABASE! You must go to the project > Benchmarks tab and click Save Benchmarks.</p>';
} else {
    echo '<table><tr><th>Project ID</th><th>Task Type</th><th>Simple hrs</th><th>Medium hrs</th><th>Complex hrs</th><th>Status</th></tr>';
    foreach ($bms as $r) {
        $allZero = ($r->simple_hours == 0 && $r->medium_hours == 0 && $r->complex_hours == 0);
        $cls = $allZero ? 'class="err"' : 'class="ok"';
        $status = $allZero ? '❌ ALL ZERO' : '✅ OK';
        echo "<tr {$cls}><td>{$r->project_id}</td><td>{$r->type_name}</td><td>{$r->simple_hours}</td><td>{$r->medium_hours}</td><td>{$r->complex_hours}</td><td>{$status}</td></tr>";
    }
    echo '</table>';
}

// 4. Tasks
echo '<h2>4. Tasks — Current State</h2><table><tr><th>ID</th><th>Type ID</th><th>Complexity</th><th>frame_count</th><th>fps</th><th>estimated_hours</th><th>Shot ID</th><th>Assigned</th></tr>';
foreach ($db->table('tasks')->select('id, task_type_id, complexity, frame_count, fps, estimated_hours, shot_id, assigned_to')->limit(20)->get()->getResult() as $r) {
    $cls = empty($r->estimated_hours) ? 'class="err"' : 'class="ok"';
    echo "<tr><td>{$r->id}</td><td>{$r->task_type_id}</td><td>{$r->complexity}</td><td>" . ($r->frame_count ?: '-') . "</td><td>" . ($r->fps ?: '-') . "</td><td {$cls}>" . ($r->estimated_hours ?: '❌ null') . "</td><td>{$r->shot_id}</td><td>" . ($r->assigned_to ?: '-') . "</td></tr>";
}
echo '</table>';

// 5. Simulation
echo '<h2>5. Simulation of one task</h2>';
$task = $db->table('tasks t')
    ->select('t.*, s.frame_count as shot_fc, s.fps as shot_fps, p.fps as proj_fps')
    ->join('shots s', 's.id = t.shot_id', 'left')
    ->join('projects p', 'p.id = t.project_id', 'left')
    ->where('t.shot_id IS NOT NULL')
    ->limit(1)->get()->getRow();

if ($task) {
    $bm = $db->table('task_benchmarks')
        ->where('project_id', $task->project_id)
        ->where('task_type_id', $task->task_type_id)
        ->get()->getRowArray();
    $complexity = $task->complexity ?: 'Medium';
    $baseHours = 0;
    if ($bm) {
        if ($complexity === 'Simple')  $baseHours = $bm['simple_hours'];
        if ($complexity === 'Medium')  $baseHours = $bm['medium_hours'];
        if ($complexity === 'Complex') $baseHours = $bm['complex_hours'];
    }
    $finalFps = $task->fps ?: ($task->shot_fps ?: ($task->proj_fps ?: 24));
    $finalFrameCount = $task->frame_count ?: $task->shot_fc;
    $durationMult = ($finalFrameCount && $finalFps > 0) ? ($finalFrameCount / $finalFps) : 0;
    $estimated = $baseHours * $durationMult;

    echo '<pre>';
    echo "Task ID: {$task->id} | Task Type ID: {$task->task_type_id}\n";
    echo "Complexity: {$complexity}\n";
    echo "Benchmark found: " . ($bm ? '✅ YES' : '❌ NO — benchmark for this project+task_type combo does not exist!') . "\n";
    if ($bm) {
        echo "Base Hours for {$complexity}: {$baseHours}" . ($baseHours == 0 ? ' ❌ ZERO → set non-zero values in Benchmarks tab' : ' ✅') . "\n";
    }
    echo "Task frame_count: " . ($task->frame_count ?: 'null') . " | Shot frame_count: " . ($task->shot_fc ?: 'null') . "\n";
    echo "Final frame_count: {$finalFrameCount}" . (empty($finalFrameCount) ? ' ❌ NO FRAMES — set frame count on shot' : ' ✅') . "\n";
    echo "Task fps: " . ($task->fps ?: 'null') . " | Shot fps: " . ($task->shot_fps ?: 'null') . " | Project fps: " . ($task->proj_fps ?: 'null') . "\n";
    echo "Final FPS used: {$finalFps}\n";
    echo "Duration = {$finalFrameCount} / {$finalFps} = {$durationMult} seconds\n";
    echo "Estimated = {$baseHours} × {$durationMult} = " . round($estimated, 4) . " hours\n";
    echo "</pre>";
} else {
    echo '<p class="warn">No shot tasks found to simulate.</p>';
}
