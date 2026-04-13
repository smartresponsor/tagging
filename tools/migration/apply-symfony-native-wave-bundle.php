<?php

// Copyright (c) 2026 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$php = PHP_BINARY ?: 'php';

$commands = [
    escapeshellarg($php) . ' ' . escapeshellarg($root . '/tools/migration/apply-symfony-native-target.php') . ' --dry-run',
    escapeshellarg($php) . ' ' . escapeshellarg($root . '/tools/migration/apply-wave2-host-minimal-bridge.php') . ' --dry-run',
];

$dryRun = in_array('--dry-run', $argv, true);
$noBackup = in_array('--no-backup', $argv, true);

if (!$dryRun) {
    $commands = [
        escapeshellarg($php) . ' ' . escapeshellarg($root . '/tools/migration/apply-symfony-native-target.php') . ($noBackup ? ' --no-backup' : ''),
        escapeshellarg($php) . ' ' . escapeshellarg($root . '/tools/migration/apply-wave2-host-minimal-bridge.php') . ($noBackup ? ' --no-backup' : ''),
    ];
}

foreach ($commands as $command) {
    echo 'run: ' . $command . PHP_EOL;
    passthru($command, $exitCode);
    if (0 !== $exitCode) {
        fwrite(STDERR, 'Command failed with exit code ' . $exitCode . PHP_EOL);
        exit($exitCode);
    }
}

echo $dryRun
    ? "Symfony-native wave bundle dry-run completed successfully.\n"
    : "Symfony-native wave bundle applied successfully.\n";
