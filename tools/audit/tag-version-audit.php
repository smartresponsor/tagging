<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$runtime = require $root . '/config/tag_runtime.php';
$surface = require $root . '/config/tag_public_surface.php';
$manifest = json_decode((string)file_get_contents($root . '/MANIFEST.json'), true);
$version = (string)($runtime['version'] ?? '');
$errors = [];
if ($version === '' || $version !== (string)($surface['version'] ?? '')) {
    $errors[] = 'runtime/public surface version mismatch';
}
if ($version !== (string)($manifest['runtime_version'] ?? '')) {
    $errors[] = 'manifest runtime_version mismatch';
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-version-audit: ok\n";
