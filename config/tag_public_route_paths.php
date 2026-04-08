<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$catalog = require __DIR__.'/tag_route_catalog.php';
$routes = is_array($catalog['routes'] ?? null) ? $catalog['routes'] : [];
$operations = [];
$paths = [];

foreach ($routes as $route) {
    if (!is_array($route) || true !== ($route['public'] ?? false)) {
        continue;
    }

    $operation = (string) ($route['operation'] ?? '');
    $path = (string) ($route['path'] ?? '');
    if ('' === $operation || '' === $path) {
        continue;
    }

    $operations[$operation] = $path;
    $paths[] = $path;
}

ksort($operations);
$paths = array_values(array_unique($paths));
sort($paths);

return [
    'operations' => $operations,
    'paths' => $paths,
];
