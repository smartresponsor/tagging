<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$openapi = file_get_contents($root . '/contracts/http/tag-openapi.yaml');
preg_match_all('/^  (\/tag[^:]*):$/m', (string) $openapi, $m);
$paths = $m[1] ?? [];
sort($paths);
$expected = ['/tag','/tag/{id}','/tag/{id}/assign','/tag/{id}/unassign','/tag/assignments','/tag/search','/tag/suggest','/tag/_status','/tag/_surface'];
sort($expected);
if ($paths !== $expected) {
    fwrite(STDERR, 'contract paths mismatch' . PHP_EOL . json_encode(['expected' => $expected, 'actual' => $paths], JSON_PRETTY_PRINT) . PHP_EOL);
    exit(1);
}
echo "tag-contract-audit: ok\n";
