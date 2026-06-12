<?php

declare(strict_types=1);

$routeSource = __DIR__ . '/platform/routes/crud/tag.yaml';
$routeMap = [];

if (is_file($routeSource)) {
    $lines = file($routeSource, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ('' === $trimmed || str_starts_with($trimmed, '#')) {
            continue;
        }

        if (1 !== preg_match(
            '/^([a-z0-9_.]+):\s*\{\s*path:\s*([^,}]+)(?:,\s*[^}]*)?\}\s*$/',
            $trimmed,
            $match,
        )) {
            continue;
        }

        $routeKey = $match[1];
        $path = trim($match[2]);

        $operation = match (true) {
            str_ends_with($routeKey, '.index') => 'index',
            str_contains($routeKey, '.show_') => 'show',
            str_contains($routeKey, '.create') => 'create',
            str_contains($routeKey, '.update_') => 'update',
            str_contains($routeKey, '.delete_') => 'delete',
            str_contains($routeKey, '.assign') => 'assign',
            str_contains($routeKey, '.unassign') => 'unassign',
            str_contains($routeKey, '.search') => 'search',
            str_contains($routeKey, '.suggest') => 'suggest',
            str_contains($routeKey, '.status') => 'status',
            str_contains($routeKey, '.metrics') => 'metrics',
            default => str_replace('tag.', '', $routeKey),
        };

        $method = match (true) {
            str_contains($routeKey, '.create'),
            str_contains($routeKey, '.assign'),
            str_contains($routeKey, '.unassign'),
            str_contains($routeKey, '.archive_'),
            str_contains($routeKey, '.restore_'),
            str_contains($routeKey, '.duplicate_'),
            str_contains($routeKey, '.approve_') => 'POST',

            str_contains($routeKey, '.update_') => 'PATCH',
            str_contains($routeKey, '.delete_') => 'DELETE',
            default => 'GET',
        };

        $routeMap[$operation] ??= sprintf('%s %s', $method, $path);
    }
}

return [
    'service' => 'tag',
    'runtime' => 'cruding-registry',
    'version' => 'dev',
    'route' => $routeMap,
    'source' => 'config/platform/routes/crud/tag.yaml',
    'example' => [
        'http' => 'public/tag/examples/tag-http-examples.http',
        'seed' => 'public/tag/examples/tag-seed-examples.http',
        'tour' => 'public/tag/examples/tag-tour-examples.http',
        'demo' => 'public/tag/demo/tag-demo-tag-demo-requests.http',
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
