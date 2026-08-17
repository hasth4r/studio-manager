<?php
/**
 * ONE-TIME MIGRATION RUNNER
 * Hit this URL once to run pending migrations, then it self-deletes.
 * URL: https://work.studioinphenix.com/run_migrate.php
 */

// Basic secret key guard — change this if you want extra safety
$secret = $_GET['key'] ?? '';
if ($secret !== 'enso8migrate2026') {
    http_response_code(403);
    die('403 Forbidden. Add ?key=enso8migrate2026 to the URL.');
}

$output = [];
$rootPath = dirname(__DIR__);

// Run spark migrate
$cmd = escapeshellcmd("php {$rootPath}/spark migrate --no-interaction 2>&1");
exec($cmd, $output, $exitCode);

// Self-delete after running
$selfDelete = @unlink(__FILE__);

header('Content-Type: text/plain; charset=utf-8');
echo "=== Migration Output ===\n";
echo implode("\n", $output);
echo "\n\n=== Exit Code: {$exitCode} ===\n";
echo $selfDelete
    ? "\n[✓] This file has self-deleted. You're all set.\n"
    : "\n[!] Could not self-delete. Please delete public/run_migrate.php manually.\n";
