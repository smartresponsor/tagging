<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../tag-bootstrap.php';
$openapi = file_get_contents($root . '/contracts/http/tag-openapi.yaml');
$routes = file_get_contents($root . '/config/routes.yaml');

preg_match_all('/^  (\/tag[^:]*):$/m', (string) $openapi, $matches);
$paths = array_values(array_unique($matches[1] ?? []));
$required = [
    '/tag/assignments/bulk',
    '/tag/assignments/bulk-to-entity',
    '/tag/search',
    '/tag/suggest',
];
$errors = [];

if (is_file($root . '/config/tag_public_route_paths.php')) {
    $errors[] = 'retired local public route projection returned';
}
if (is_file($root . '/config/tag_public_surface.php')) {
    $errors[] = 'retired local public surface projection returned';
}
if (!is_string($routes) || !str_contains($routes, 'Cruding bundle')) {
    $errors[] = 'Cruding-owned route declaration missing';
}
if (str_contains((string) $routes, 'tagging_native') || is_file($root . '/config/routes/tagging_native.yaml')) {
    $errors[] = 'retired local route projection returned';
}
$missing = array_values(array_diff($required, $paths));
if ($missing !== []) {
    $errors[] = 'required public contract paths missing: ' . implode(', ', $missing);
}
if (in_array('/tag/_webhooks', $paths, true)) {
    $errors[] = 'private webhook route leaked into public contract';
}

if ($errors !== []) {
    fwrite(STDERR, 'surface truth mismatch' . PHP_EOL . implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-surface-audit: ok\n";
