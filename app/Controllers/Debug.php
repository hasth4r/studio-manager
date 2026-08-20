<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Debug extends BaseController
{
    public function calc()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();

        echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:20px}table{border-collapse:collapse;width:100%;margin-bottom:20px}th,td{border:1px solid #444;padding:6px 10px;text-align:left}th{background:#222}.ok{color:#4f4}.err{color:#f44}.warn{color:#fa4}h2{color:#3da2ff;margin-top:30px}pre{background:#1a1a1a;padding:15px;border-radius:6px;border:1px solid #444;white-space:pre-wrap}</style>';
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

        // 3. Benchmarks — THE KEY CHECK
        echo '<h2>3. Task Benchmarks</h2>';
        $bms = $db->table('task_benchmarks')
            ->select('task_benchmarks.*, task_types.name as type_name')
            ->join('task_types', 'task_types.id = task_benchmarks.task_type_id', 'left')
            ->get()->getResult();

        if (empty($bms)) {
            echo '<p class="err" style="font-size:18px;padding:15px;background:#2a1215;border:1px solid #f44;border-radius:6px">❌ NO BENCHMARKS EXIST! Go to Project → Benchmarks tab → fill in hours → Save Benchmarks.</p>';
        } else {
            echo '<table><tr><th>Project ID</th><th>Task Type</th><th>Simple hrs</th><th>Medium hrs</th><th>Complex hrs</th><th>Status</th></tr>';
            foreach ($bms as $r) {
                $allZero = ($r->simple_hours == 0 && $r->medium_hours == 0 && $r->complex_hours == 0);
                $cls = $allZero ? 'class="err"' : 'class="ok"';
                $status = $allZero ? '❌ ALL ZERO — fix in Benchmarks tab' : '✅ OK';
                echo "<tr {$cls}><td>{$r->project_id}</td><td>{$r->type_name}</td><td>{$r->simple_hours}</td><td>{$r->medium_hours}</td><td>{$r->complex_hours}</td><td>{$status}</td></tr>";
            }
            echo '</table>';
        }

        // 4. Tasks
        echo '<h2>4. Tasks — Current State</h2><table><tr><th>ID</th><th>Type ID</th><th>Complexity</th><th>frame_count</th><th>fps</th><th>estimated_hours</th><th>Shot ID</th></tr>';
        foreach ($db->table('tasks')->select('id, task_type_id, complexity, frame_count, fps, estimated_hours, shot_id')->limit(20)->get()->getResult() as $r) {
            $cls = empty($r->estimated_hours) ? 'class="err"' : 'class="ok"';
            echo "<tr><td>{$r->id}</td><td>{$r->task_type_id}</td><td>{$r->complexity}</td><td>" . ($r->frame_count ?: '-') . "</td><td>" . ($r->fps ?: '-') . "</td><td {$cls}>" . ($r->estimated_hours ?: '❌ null') . "</td><td>" . ($r->shot_id ?: '-') . "</td></tr>";
        }
        echo '</table>';

        // 5. Simulate one shot task
        echo '<h2>5. Simulation — First Shot Task</h2>';
        $task = $db->table('tasks')
            ->select('tasks.*, shots.frame_count as shot_fc, shots.fps as shot_fps, projects.fps as proj_fps')
            ->join('shots', 'shots.id = tasks.shot_id', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('tasks.shot_id IS NOT NULL')
            ->limit(1)->get()->getRow();

        if ($task) {
            $bm = $db->table('task_benchmarks')
                ->where('project_id', $task->project_id)
                ->where('task_type_id', $task->task_type_id)
                ->get()->getRowArray();

            $complexity    = $task->complexity ?: 'Medium';
            $baseHours     = 0;
            if ($bm) {
                if ($complexity === 'Simple')  $baseHours = $bm['simple_hours'];
                if ($complexity === 'Medium')  $baseHours = $bm['medium_hours'];
                if ($complexity === 'Complex') $baseHours = $bm['complex_hours'];
            }
            $finalFps        = $task->fps ?: ($task->shot_fps ?: ($task->proj_fps ?: 24));
            $finalFrameCount = $task->frame_count ?: $task->shot_fc;
            $durationMult    = ($finalFrameCount && $finalFps > 0) ? ($finalFrameCount / $finalFps) : 0;
            $estimated       = $baseHours * $durationMult;

            echo '<pre>';
            echo "Task ID:        {$task->id}\n";
            echo "Task Type ID:   {$task->task_type_id}\n";
            echo "Project ID:     {$task->project_id}\n";
            echo "Complexity:     {$complexity}\n\n";

            echo "Benchmark:      " . ($bm ? '✅ FOUND' : '❌ NOT FOUND for this project + task_type combo') . "\n";
            if ($bm) {
                echo "Base Hours:     {$baseHours}" . ($baseHours == 0 ? "  ❌ ZERO — set non-zero values in Benchmarks tab" : "  ✅") . "\n";
            }
            echo "\nShot fc:        " . ($task->shot_fc ?: 'null') . "\n";
            echo "Task fc:        " . ($task->frame_count ?: 'null') . "\n";
            echo "Final fc used:  {$finalFrameCount}" . (empty($finalFrameCount) ? "  ❌ NO FRAMES — set frame_count on Shot settings" : "  ✅") . "\n";
            echo "\nProject fps:    " . ($task->proj_fps ?: 'null') . "\n";
            echo "Final FPS used: {$finalFps}\n";
            echo "\nDuration:       {$finalFrameCount} ÷ {$finalFps} = {$durationMult} seconds\n";
            echo "RESULT:         {$baseHours} × {$durationMult} = " . round($estimated, 4) . " hours\n";
            echo ($estimated > 0 ? "\n✅ Should calculate correctly." : "\n❌ Result is 0 — fix the items marked above.") . "\n";
            echo '</pre>';
        } else {
            echo '<p class="warn">No shot tasks found.</p>';
        }
    }

    public function seedBenchmarks()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();

        $projects  = $db->table('projects')->select('id, name')->get()->getResult();
        $taskTypes = $db->table('task_types')->select('id, name')->get()->getResult();

        $inserted = 0;
        $skipped  = 0;

        foreach ($projects as $project) {
            foreach ($taskTypes as $type) {
                $existing = $db->table('task_benchmarks')
                    ->where('project_id', $project->id)
                    ->where('task_type_id', $type->id)
                    ->get()->getRow();

                if (!$existing) {
                    $db->table('task_benchmarks')->insert([
                        'project_id'    => $project->id,
                        'task_type_id'  => $type->id,
                        'simple_hours'  => 1.0,
                        'medium_hours'  => 3.0,
                        'complex_hours' => 6.0,
                    ]);
                    $inserted++;
                } else {
                    $skipped++;
                }
            }
        }

        echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:40px}a{color:#3da2ff}.ok{color:#4f4}.warn{color:#fa4}</style>';
        echo '<h2 class="ok">✅ Default Benchmarks Seeded!</h2>';
        echo "<p>Inserted: <strong class='ok'>{$inserted}</strong> benchmark rows (Simple=1hr, Medium=3hrs, Complex=6hrs)</p>";
        echo "<p>Skipped (already existed): <strong class='warn'>{$skipped}</strong></p>";
        echo '<p>You can adjust these values per task type in any Project → Benchmarks tab.</p>';
        echo '<br><a href="/debug/calc">→ Run diagnostic again</a> &nbsp;|&nbsp; <a href="/admin/projects">→ Go to Projects</a>';
    }
}
