<?php

declare(strict_types=1);

$openApiPath = dirname(__DIR__) . '/contracts/http/tag-openapi.yaml';
$openApi = is_file($openApiPath) ? (string) file_get_contents($openApiPath) : '';
preg_match_all('/^  (\/tag[^:]*):$/m', $openApi, $matches);
$paths = array_values(array_unique($matches[1] ?? []));

$operationByPath = [
    '/tag/_status' => 'status',
    '/tag/_surface' => 'discovery',
    '/tag/assignments/bulk' => 'assignments_bulk',
    '/tag/assignments/bulk-to-entity' => 'assignments_bulk_to_entity',
    '/tag/search' => 'search',
    '/tag/suggest' => 'suggest',
];
$methodByPath = [
    '/tag/assignments/bulk' => 'POST',
    '/tag/assignments/bulk-to-entity' => 'POST',
];

$routeMap = [];
$publicSurface = [];
foreach ($paths as $path) {
    if ('/tag/_webhooks' === $path) {
        continue;
    }

    $method = $methodByPath[$path] ?? 'GET';
    $operation = $operationByPath[$path] ?? trim(str_replace(['/', '-', '{', '}'], ['_', '_', '', ''], $path), '_');
    $name = str_replace('_', ' ', $operation);

    $routeMap[$operation] = in_array($operation, ['status', 'discovery'], true)
        ? $path
        : $method . ' ' . $path;
    $publicSurface[] = [
        'method' => $method,
        'path' => $path,
        'nameEntity' => $name,
    ];
}

return [
    'service' => 'tag',
    'runtime' => 'hosted-package',
    'version' => 'dev',
    'route' => $routeMap,
    'example' => [],
    'doc' => ['openapi' => 'contracts/http/tag-openapi.yaml'],
    'public_surface' => $publicSurface,
];
