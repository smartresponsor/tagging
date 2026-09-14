<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../tag-bootstrap.php';
$runtime = require $root . '/config/tag_runtime.php';
$version = (string) ($runtime['version'] ?? '');
$errors = [];
if ('' === $version) {
    $errors[] = 'runtime version is missing';
}
if (is_file($root . '/config/tag_public_surface.php')) {
    $errors[] = 'legacy public surface config remains';
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-version-audit: ok\n";
