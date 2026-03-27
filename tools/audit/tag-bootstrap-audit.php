<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$container = require $root . '/host-minimal/bootstrap.php';
$required = ['runtime','idempotencyMiddleware','statusController','surfaceController','tagController','assignController','searchController','suggestController','assignmentReadController'];
$errors = [];
foreach ($required as $key) {
    if (!isset($container[$key]) || !is_callable($container[$key])) {
        $errors[] = 'missing container service ' . $key;
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-bootstrap-audit: ok\n";
