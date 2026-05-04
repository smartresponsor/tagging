<?php

declare(strict_types=1);

$catalog = require __DIR__ . '/tag_route_catalog.php';
$routes = is_array($catalog['routes'] ?? null) ? $catalog['routes'] : [];
$routeMap = [];
foreach ($routes as $route) {
    if (!is_array($route) || true !== ($route['public'] ?? false)) {
        continue;
    }

    $operation = (string) ($route['operation'] ?? '');
    $method = (string) ($route['method'] ?? 'GET');
    $path = (string) ($route['path'] ?? '');
    if ('' === $operation || '' === $path) {
        continue;
    }

    $routeMap[$operation] = in_array($operation, ['status', 'discovery'], true)
        ? $path
        : sprintf('%s %s', $method, $path);
}

return [
    'service' => (string) ($catalog['service'] ?? 'tag'),
    'runtime' => (string) ($catalog['runtime'] ?? 'hosted-package'),
    'version' => (string) ($catalog['version'] ?? 'dev'),
    'route' => $routeMap,
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
