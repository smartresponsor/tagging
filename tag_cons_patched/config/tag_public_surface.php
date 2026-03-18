<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

return [
    'service' => 'tag',
    'version' => 'p121-consolidation-minimal-surface',
    'public_surface' => [
        ['method' => 'POST', 'path' => '/tag'],
        ['method' => 'GET', 'path' => '/tag/{id}'],
        ['method' => 'PATCH', 'path' => '/tag/{id}'],
        ['method' => 'DELETE', 'path' => '/tag/{id}'],
        ['method' => 'POST', 'path' => '/tag/{id}/assign'],
        ['method' => 'POST', 'path' => '/tag/{id}/unassign'],
        ['method' => 'GET', 'path' => '/tag/assignments'],
        ['method' => 'GET', 'path' => '/tag/search'],
        ['method' => 'GET', 'path' => '/tag/suggest'],
        ['method' => 'GET', 'path' => '/tag/_status'],
        ['method' => 'GET', 'path' => '/tag/_surface'],
    ],
];
