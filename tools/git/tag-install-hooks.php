<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = $root . DIRECTORY_SEPARATOR . '.githooks' . DIRECTORY_SEPARATOR . 'pre-commit';
$targetDir = $root . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'hooks';
$target = $targetDir . DIRECTORY_SEPARATOR . 'pre-commit';

if (!is_file($source)) {
    fwrite(STDERR, "Hook template not found: {$source}\n");
    exit(1);
}

if (!is_dir($targetDir)) {
    fwrite(STDERR, "Git hooks directory not found: {$targetDir}\n");
    fwrite(STDERR, "Initialize this inside a git working tree.\n");
    exit(1);
}

if (!copy($source, $target)) {
    fwrite(STDERR, "Failed to copy hook to {$target}\n");
    exit(1);
}

if (DIRECTORY_SEPARATOR === '/') {
    @chmod($target, 0755);
}

fwrite(STDOUT, "Installed pre-commit hook to {$target}\n");
