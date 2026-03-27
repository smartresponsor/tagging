<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$routes = file_get_contents($root . '/tag.yaml');
$openapi = file_get_contents($root . '/contracts/http/tag-openapi.yaml');
$expected = [
    '/tag', '/tag/{id}', '/tag/{id}/assign', '/tag/{id}/unassign', '/tag/assignments', '/tag/search', '/tag/suggest', '/tag/_status', '/tag/_surface',
];
$forbidden = ['/tag/assign-bulk', '/tag/assignment/bulk', '/tag/{id}/synonym', '/tag/redirect/{fromId}'];
$errors = [];
foreach ($expected as $path) {
    if ($routes === false || strpos($routes, 'path: ' . $path) === false) {
        $errors[] = 'missing route ' . $path;
    }
    if ($openapi === false || strpos($openapi, '  ' . $path . ':') === false) {
        $errors[] = 'missing contract ' . $path;
    }
}
foreach ($forbidden as $path) {
    if (($routes !== false && strpos($routes, 'path: ' . $path) !== false) || ($openapi !== false && strpos($openapi, '  ' . $path . ':') !== false)) {
        $errors[] = 'forbidden public path ' . $path;
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-surface-audit: ok\n";
