<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$quota = file_get_contents($root . '/tag_quota.yaml') ?: '';
$assignment = file_get_contents($root . '/tag_assignment.yaml') ?: '';
$search = file_get_contents($root . '/config/tag_search.yaml') ?: '';
$errors = [];
if (!preg_match('/^enforce:\s+(true|false)\s*$/m', $quota)) {
    $errors[] = 'tag_quota enforce must be boolean';
}
if (strpos($assignment, 'driver: pdo') === false) {
    $errors[] = 'tag_assignment must declare pdo driver';
}
if (strpos($search, 'prefer_db: true') === false || strpos($search, 'driver: pdo') === false) {
    $errors[] = 'tag_search must prefer pdo';
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-config-audit: ok\n";
