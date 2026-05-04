<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$publicRoutePaths = require $root . '/config/tag_public_route_paths.php';
$expected = is_array($publicRoutePaths['paths'] ?? null) ? $publicRoutePaths['paths'] : [];
$openapi = file_get_contents($root . '/contracts/http/tag-openapi.yaml');
preg_match_all('/^  (\/tag[^:]*):$/m', (string) $openapi, $m);
$actual = $m[1] ?? [];
sort($actual);
sort($expected);
if ($actual !== $expected) {
    $missing = array_values(array_diff($expected, $actual));
    $extra = array_values(array_diff($actual, $expected));
    fwrite(STDERR, 'contract paths mismatch' . PHP_EOL . json_encode([
        'expected' => $expected,
        'actual' => $actual,
        'missing' => $missing,
        'extra' => $extra,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}
echo "tag-contract-audit: ok\n";
