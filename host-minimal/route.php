<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use App\Http\Api\Tag\Responder\JsonResponder;

/**
 * @param array<string, callable():mixed> $container
 * @return callable(string,string,array<string,mixed>):array{0:int,1:array<string,string>,2:string}
 */
return static function (array $container): callable {
    $json = new JsonResponder();
    $controller = static fn(string $id): object => $container[$id]();
    $runtimeVersion = static fn(): string => (string) (($container['runtime']()['version'] ?? 'dev'));
    $invoke = static fn(string $serviceId, string $method, array $norm, mixed ...$args): array => $controller($serviceId)->{$method}($norm, ...$args);
    $route = static fn(string $method, string $pattern, callable $handler): array => [
        'method' => $method,
        'pattern' => $pattern,
        'handler' => $handler,
    ];
    $statusRoute = static fn(string $path, string $header, string $controllerId, string $method): array => $route(
        'GET',
        $path,
        static fn(): array => $json->respond(
            200,
            $controller($controllerId)->{$method}(),
            [
                $header => $runtimeVersion(),
                'Cache-Control' => 'no-store',
            ],
        ),
    );

    $routes = [
        $route('POST', '#^/tag$#', static fn(array $norm): array => $invoke('tagController', 'create', $norm)),
        $route('GET', '#^/tag/([A-Za-z0-9]{26})$#', static fn(array $norm, array $m): array => $invoke('tagController', 'get', $norm, $m[1])),
        $route('PATCH', '#^/tag/([A-Za-z0-9]{26})$#', static fn(array $norm, array $m): array => $invoke('tagController', 'patch', $norm, $m[1])),
        $route('DELETE', '#^/tag/([A-Za-z0-9]{26})$#', static fn(array $norm, array $m): array => $invoke('tagController', 'delete', $norm, $m[1])),
        $route('POST', '#^/tag/([A-Za-z0-9]{26})/assign$#', static fn(array $norm, array $m): array => $invoke('assignController', 'assign', $norm, $m[1])),
        $route('POST', '#^/tag/([A-Za-z0-9]{26})/unassign$#', static fn(array $norm, array $m): array => $invoke('assignController', 'unassign', $norm, $m[1])),
        $route('POST', '#^/tag/assignments/bulk$#', static fn(array $norm): array => $invoke('assignController', 'bulk', $norm)),
        $route('POST', '#^/tag/assignments/bulk-to-entity$#', static fn(array $norm): array => $invoke('assignController', 'assignBulkToEntity', $norm)),
        $route('GET', '#^/tag/assignments$#', static fn(array $norm): array => $invoke('assignmentReadController', 'listByEntity', $norm)),
        $route('GET', '#^/tag/search$#', static fn(array $norm): array => $invoke('searchController', 'get', $norm)),
        $route('GET', '#^/tag/suggest$#', static fn(array $norm): array => $invoke('suggestController', 'get', $norm)),
        $route('GET', '#^/tag/_webhooks$#', static fn(array $norm): array => $invoke('webhookController', 'list', $norm)),
        $route('POST', '#^/tag/_webhooks/subscribe$#', static fn(array $norm): array => $invoke('webhookController', 'subscribe', $norm)),
        $route('POST', '#^/tag/_webhooks/test$#', static fn(array $norm): array => $invoke('webhookController', 'test', $norm)),
        $statusRoute('#^/tag/_status$#', 'X-Tag-Version', 'statusController', 'status'),
        $statusRoute('#^/tag/_surface$#', 'X-Tag-Surface-Version', 'surfaceController', 'surface'),
    ];

    return static function (string $method, string $path, array $norm) use ($routes, $json): array {
        if ($method === 'OPTIONS') {
            return $json->empty(204, ['Allow' => 'GET,POST,PATCH,DELETE,OPTIONS', 'Cache-Control' => 'no-store']);
        }

        foreach ($routes as $route) {
            if ($route['method'] !== $method || !preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            return $route['handler']($norm, $matches);
        }

        return $json->reject(404, 'not_found');
    };
};
