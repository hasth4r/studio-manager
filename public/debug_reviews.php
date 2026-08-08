<?php
// Diagnostic tool for review workflow
// Visit: http://localhost/eso8manager_v0.0.1/public/debug_reviews.php

$db = \Config\Database::connect();

header('Content-Type: text/html');
echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:20px}table{border-collapse:collapse;width:100%;margin-bottom:20px}th,td{border:1px solid #444;padding:6px 10px;text-align:left}th{background:#222}.ok{color:#4f4;background:#0a1a0a}.err{color:#f44;background:#1a0a0a}.warn{color:#fa4;background:#1a1500}h2{color:#3da2ff;margin-top:30px}pre{background:#1a1a1a;padding:15px;border-radius:6px;border:1px solid #444}</style>';
echo '<h1>🔍 Review Workflow Debug</h1>';

// 1. Reviews table
echo '<h2>1. All Reviews</h2>';
$reviews = $db->table('reviews r')
    ->select('r.id, r.vfx_task_assignment_id, r.status, r.version_string, r.created_at, r.updated_at, u.name as artist')
    ->join('users u', 'u.id = r.user_id', 'left')
    ->get()->getResult();

if (empty($reviews)) {
    echo '<p class="err">❌ No reviews found in database</p>';
} else {
    echo '<table><tr><th>Review ID</th><th>Task ID (vfx_task_assignment_id)</th><th>Version</th><th>Review Status</th><th>Artist</th><th>Updated</th></tr>';
    foreach ($reviews as $r) {
        $cls = $r->status === 'revision_needed' ? 'class="warn"' : ($r->status === 'approved' ? 'class="ok"' : '');
        echo "<tr {$cls}><td>{$r->id}</td><td>{$r->vfx_task_assignment_id}</td><td>{$r->version_string}</td><td>{$r->status}</td><td>{$r->artist}</td><td>{$r->updated_at}</td></tr>";
    }
    echo '</table>';
}

// 2. Tasks table (linked to reviews)
echo '<h2>2. Tasks (especially revision_needed)</h2>';
$tasks = $db->table('tasks t')
    ->select('t.id, t.status, t.assigned_to, t.updated_at, u.name as artist, tt.name as task_type, p.name as project')
    ->join('users u', 'u.id = t.assigned_to', 'left')
    ->join('task_types tt', 'tt.id = t.task_type_id', 'left')
    ->join('projects p', 'p.id = t.project_id', 'left')
    ->get()->getResult();

if (empty($tasks)) {
    echo '<p class="err">❌ No tasks found</p>';
} else {
    echo '<table><tr><th>Task ID</th><th>Status</th><th>Artist</th><th>Task Type</th><th>Project</th><th>Updated</th></tr>';
    foreach ($tasks as $t) {
        $cls = $t->status === 'revision_needed' ? 'class="warn"' : ($t->status === 'completed' ? 'class="ok"' : '');
        echo "<tr {$cls}><td>{$t->id}</td><td>{$t->status}</td><td>{$t->artist}</td><td>{$t->task_type}</td><td>{$t->project}</td><td>{$t->updated_at}</td></tr>";
    }
    echo '</table>';
}

// 3. Cross-check: Do review's vfx_task_assignment_id match actual task IDs?
echo '<h2>3. Link Check: review.vfx_task_assignment_id → tasks.id</h2>';
echo '<table><tr><th>Review ID</th><th>Points to Task ID</th><th>Task Exists?</th><th>Task Status</th><th>Match?</th></tr>';
foreach ($reviews as $r) {
    $linkedTask = $db->table('tasks')->where('id', $r->vfx_task_assignment_id)->get()->getRow();
    $exists = $linkedTask ? '✅ YES' : '❌ NO';
    $taskStatus = $linkedTask ? $linkedTask->status : 'N/A';
    $cls = $linkedTask ? '' : 'class="err"';
    $match = ($linkedTask && $linkedTask->status === $r->status) ? '🔄 Synced' : '⚠️ Out of sync';
    echo "<tr {$cls}><td>{$r->id}</td><td>{$r->vfx_task_assignment_id}</td><td>{$exists}</td><td>{$taskStatus}</td><td>{$match}</td></tr>";
}
echo '</table>';

// 4. Comments
echo '<h2>4. Review Comments (Annotations)</h2>';
$comments = $db->table('review_comments rc')
    ->select('rc.id, rc.review_id, rc.comment_text, rc.timecode, rc.resolution_status, rc.created_at, u.name as reviewer')
    ->join('users u', 'u.id = rc.user_id', 'left')
    ->limit(20)
    ->get()->getResult();

if (empty($comments)) {
    echo '<p class="warn">⚠️ No comments found</p>';
} else {
    echo '<table><tr><th>Comment ID</th><th>Review ID</th><th>Reviewer</th><th>Timecode</th><th>Comment</th><th>Resolution</th></tr>';
    foreach ($comments as $c) {
        $cls = $c->resolution_status === 'pending' ? 'class="warn"' : 'class="ok"';
        echo "<tr {$cls}><td>{$c->id}</td><td>{$c->review_id}</td><td>{$c->reviewer}</td><td>{$c->timecode}</td><td>" . substr($c->comment_text, 0, 60) . "</td><td>{$c->resolution_status}</td></tr>";
    }
    echo '</table>';
}
