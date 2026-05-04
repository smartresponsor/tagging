<?php

// Copyright (c) 2026 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$sourceRoot = $root . '/migration/symfony-native-target';

if (!is_dir($sourceRoot)) {
    fwrite(STDERR, "Target migration directory not found: {$sourceRoot}\n");
    exit(1);
}

$map = [
    $sourceRoot . '/composer.json' => $root . '/composer.json',
    $sourceRoot . '/public/index.php' => $root . '/public/index.php',
    $sourceRoot . '/tag.yaml' => $root . '/tag.yaml',
    $sourceRoot . '/config/tag_runtime.php' => $root . '/config/tag_runtime.php',
    $sourceRoot . '/docs/http/http-wiring.md' => $root . '/docs/http/http-wiring.md',
];

$dryRun = in_array('--dry-run', $argv, true);
$backup = !in_array('--no-backup', $argv, true);
$timestamp = date('Ymd-His');
$backupRoot = $root . '/var/migration-backup/' . $timestamp;

foreach ($map as $source => $target) {
    if (!is_file($source)) {
        fwrite(STDERR, "Missing source file: {$source}\n");
        exit(1);
    }

    $targetDir = dirname($target);
    if (!is_dir($targetDir) && !$dryRun) {
        mkdir($targetDir, 0775, true);
    }

    echo ($dryRun ? '[DRY-RUN] ' : '') . "replace {$target} <= {$source}\n";

    if ($dryRun) {
        continue;
    }

    if ($backup && file_exists($target)) {
        $backupTarget = $backupRoot . '/' . ltrim(str_replace($root, '', $target), '/');
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
}

echo "Symfony-native target files applied successfully.\n";
if ($backup && !$dryRun) {
    echo "Backup stored under: {$backupRoot}\n";
}
