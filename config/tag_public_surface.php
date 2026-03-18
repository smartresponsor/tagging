<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

return [
    'service' => 'tag',
    'runtime' => 'host-minimal',
    'version' => 'p111-public-surface-reconcile',
    'route' => [
        'status' => '/tag/_status',
        'discovery' => '/tag/_surface',
        'create' => 'POST /tag',
        'read' => 'GET /tag/{id}',
        'patch' => 'PATCH /tag/{id}',
        'delete' => 'DELETE /tag/{id}',
        'assign' => 'POST /tag/{id}/assign',
        'unassign' => 'POST /tag/{id}/unassign',
        'assignments' => 'GET /tag/assignments',
        'search' => 'GET /tag/search',
        'suggest' => 'GET /tag/suggest',
    ],
    'example' => [
        'http' => 'public/tag/examples/http.http',
        'seed' => 'public/tag/examples/seed.http',
        'tour' => 'public/tag/examples/tour.http',
        'demo' => 'public/tag/demo/requests.http',
    ],
    'doc' => [
        'readme' => 'README.md',
        'demo' => 'docs/demo/tag-quick-demo.md',
        'fixture' => 'docs/fixtures/demo.md',
        'seed' => 'docs/seed/tag-seed.md',
        'admin' => 'docs/admin/user-guide.md',
        'checklist' => 'docs/public/tag-public-ready-checklist.md',
        'sdk' => 'sdk/README.md',
    ],
];
