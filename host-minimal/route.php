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

    $routes = [
        ['method' => 'POST', 'pattern' => '#^/tag$#', 'handler' => static fn(array $norm): array => $container['tagController']()->create($norm)],
        ['method' => 'GET', 'pattern' => '#^/tag/([A-Za-z0-9]{26})$#', 'handler' => static fn(array $norm, array $m): array => $container['tagController']()->get($norm, $m[1])],
        ['method' => 'PATCH', 'pattern' => '#^/tag/([A-Za-z0-9]{26})$#', 'handler' => static fn(array $norm, array $m): array => $container['tagController']()->patch($norm, $m[1])],
        ['method' => 'DELETE', 'pattern' => '#^/tag/([A-Za-z0-9]{26})$#', 'handler' => static fn(array $norm, array $m): array => $container['tagController']()->delete($norm, $m[1])],
        ['method' => 'POST', 'pattern' => '#^/tag/([A-Za-z0-9]{26})/assign$#', 'handler' => static fn(array $norm, array $m): array => $container['assignController']()->assign($norm, $m[1])],
        ['method' => 'POST', 'pattern' => '#^/tag/([A-Za-z0-9]{26})/unassign$#', 'handler' => static fn(array $norm, array $m): array => $container['assignController']()->unassign($norm, $m[1])],
        ['method' => 'GET', 'pattern' => '#^/tag/assignments$#', 'handler' => static fn(array $norm): array => $container['assignmentReadController']()->listByEntity($norm)],
        ['method' => 'GET', 'pattern' => '#^/tag/search$#', 'handler' => static fn(array $norm): array => $container['searchController']()->get($norm)],
        ['method' => 'GET', 'pattern' => '#^/tag/suggest$#', 'handler' => static fn(array $norm): array => $container['suggestController']()->get($norm)],
        ['method' => 'GET', 'pattern' => '#^/tag/_webhooks$#', 'handler' => static fn(array $norm): array => $container['webhookController']()->list($norm)],
        ['method' => 'POST', 'pattern' => '#^/tag/_webhooks/subscribe$#', 'handler' => static fn(array $norm): array => $container['webhookController']()->subscribe($norm)],
        ['method' => 'POST', 'pattern' => '#^/tag/_webhooks/test$#', 'handler' => static fn(array $norm): array => $container['webhookController']()->test($norm)],
        ['method' => 'GET', 'pattern' => '#^/tag/_status$#', 'handler' => static fn(): array => $json->respond(
            200,
            $container['statusController']()->status(),
            [
                'X-Tag-Version' => (string) (($container['runtime']()['version'] ?? 'dev')),
                'Cache-Control' => 'no-store',
            ],
        )],
        ['method' => 'GET', 'pattern' => '#^/tag/_surface$#', 'handler' => static fn(): array => $json->respond(
            200,
            $container['surfaceController']()->surface(),
            [
                'X-Tag-Surface-Version' => (string) (($container['runtime']()['version'] ?? 'dev')),
                'Cache-Control' => 'no-store',
            ],
        )],
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
