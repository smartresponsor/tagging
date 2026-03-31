<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$surface = require __DIR__.'/tag_public_surface.php';
if (!is_array($surface)) {
    return [
        'service' => 'tag',
        'version' => 'dev',
        'route' => [],
        'example' => [],
        'doc' => [],
        'public_surface' => [],
    ];
}

$routeMap = is_array($surface['route'] ?? null) ? $surface['route'] : [];
$methodByOperation = [
    'status' => 'GET',
    'discovery' => 'GET',
    'create' => 'POST',
    'read' => 'GET',
    'patch' => 'PATCH',
    'delete' => 'DELETE',
    'assign' => 'POST',
    'unassign' => 'POST',
    'assignments_bulk' => 'POST',
    'assignments_bulk_to_entity' => 'POST',
    'assignments' => 'GET',
    'search' => 'GET',
    'suggest' => 'GET',
];

$publicSurface = [];
foreach ($methodByOperation as $operation => $defaultMethod) {
    $route = $routeMap[$operation] ?? null;
    if (!is_string($route) || '' === $route) {
        continue;
    }

    $method = $defaultMethod;
    $path = $route;

    if (1 === preg_match('/^([A-Z]+)\s+(.+)$/', $route, $m)) {
        $method = $m[1];
        $path = $m[2];
    }

    $publicSurface[] = [
        'method' => $method,
        'path' => $path,
        'name' => str_replace('_', ' ', $operation),
    ];
}

return [
    'service' => (string) ($surface['service'] ?? 'tag'),
    'runtime' => (string) ($surface['runtime'] ?? 'host-minimal'),
    'version' => (string) ($surface['version'] ?? 'dev'),
    'route' => $routeMap,
    'example' => is_array($surface['example'] ?? null) ? $surface['example'] : [],
    'doc' => is_array($surface['doc'] ?? null) ? $surface['doc'] : [],
    'public_surface' => $publicSurface,
];
