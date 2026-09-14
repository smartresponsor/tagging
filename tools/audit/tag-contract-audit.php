<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../tag-bootstrap.php';
$openapi = file_get_contents($root . '/contracts/http/tag-openapi.yaml');
preg_match_all('/^  (\/tag[^:]*):$/m', (string) $openapi, $matches);
$paths = array_values(array_unique($matches[1] ?? []));
$required = [
    '/tag/assignments/bulk',
    '/tag/assignments/bulk-to-entity',
    '/tag/search',
    '/tag/suggest',
];
$missing = array_values(array_diff($required, $paths));
if ($missing !== [] || in_array('/tag/_webhooks', $paths, true)) {
    fwrite(STDERR, 'contract paths mismatch' . PHP_EOL . json_encode([
        'required' => $required,
        'actual' => $paths,
        'missing' => $missing,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}
echo "tag-contract-audit: ok\n";
