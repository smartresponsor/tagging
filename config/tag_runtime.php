<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

return [
    'service' => 'tag',
    'version' => 'p111-public-surface-reconcile',
    'route' => [
        'status' => '/tag/_status',
        'discovery' => '/tag/_surface',
    ],
    'public_surface' => [
        ['method' => 'POST', 'path' => '/tag', 'name' => 'create tag'],
        ['method' => 'GET', 'path' => '/tag/{id}', 'name' => 'get tag'],
        ['method' => 'PATCH', 'path' => '/tag/{id}', 'name' => 'patch tag'],
        ['method' => 'DELETE', 'path' => '/tag/{id}', 'name' => 'delete tag'],
        ['method' => 'POST', 'path' => '/tag/{id}/assign', 'name' => 'assign tag'],
        ['method' => 'POST', 'path' => '/tag/{id}/unassign', 'name' => 'unassign tag'],
        ['method' => 'GET', 'path' => '/tag/assignments', 'name' => 'list entity assignments'],
        ['method' => 'GET', 'path' => '/tag/search', 'name' => 'search tags'],
        ['method' => 'GET', 'path' => '/tag/suggest', 'name' => 'suggest tags'],
        ['method' => 'GET', 'path' => '/tag/_status', 'name' => 'status'],
        ['method' => 'GET', 'path' => '/tag/_surface', 'name' => 'surface catalog'],
    ],
];
