<?php

// Copyright (c) 2026 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = $root . '/migration/symfony-native-target/host-minimal/bootstrap.symfony-bridge.v2.php';
$target = $root . '/host-minimal/bootstrap.php';

if (!is_file($source)) {
    fwrite(STDERR, "Missing source bridge file: {$source}\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv, true);
$backup = !in_array('--no-backup', $argv, true);
$timestamp = date('Ymd-His');
$backupRoot = $root . '/var/migration-backup/' . $timestamp;

$targetDir = dirname($target);
if (!is_dir($targetDir) && !$dryRun) {
    mkdir($targetDir, 0775, true);
}

echo ($dryRun ? '[DRY-RUN] ' : '') . "replace {$target} <= {$source}\n";

if ($dryRun) {
    exit(0);
}

if ($backup && file_exists($target)) {
    $backupTarget = $backupRoot . '/host-minimal/bootstrap.php';
    $backupDir = dirname($backupTarget);
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0775, true);
    }
    copy($target, $backupTarget);
}

if (!copy($source, $target)) {
    fwrite(STDERR, "Failed to replace {$target}\n");
    exit(1);
}

echo "Wave 2 host-minimal Symfony bridge applied successfully.\n";
if ($backup) {
    echo "Backup stored under: {$backupRoot}\n";
}
