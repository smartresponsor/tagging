<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$checks = [
    $root . '/tools/audit/tag-surface-audit.php',
    $root . '/tools/audit/tag-contract-audit.php',
    $root . '/tools/audit/tag-openapi-semantics-audit.php',
    $root . '/tools/audit/tag-generated-openapi-surface-audit.php',
    $root . '/tools/audit/tag-antora-surface-audit.php',
    $root . '/tools/audit/tag-route-controller-audit.php',
    $root . '/tools/audit/tag-bootstrap-audit.php',
    $root . '/tools/audit/tag-bootstrap-runtime-audit.php',
    $root . '/tools/audit/tag-config-audit.php',
    $root . '/tools/audit/tag-sdk-audit.php',
    $root . '/tools/audit/tag-demo-truth-pack-audit.php',
    $root . '/tools/audit/tag-release-assets-audit.php',
    $root . '/tools/audit/tag-release-grade-portrait-audit.php',
    $root . '/tools/audit/tag-version-audit.php',
    $root . '/tools/seed/tag-fixture-validate.php',
];
$errors = [];
foreach ($checks as $check) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($check) . ' 2>&1';
    exec($cmd, $out, $code);
    if ($code !== 0) {
        $errors[] = basename($check) . PHP_EOL . implode(PHP_EOL, $out);
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL . PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-preflight: ok\n";
