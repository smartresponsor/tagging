<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * @param array<string, callable():mixed> $container
 * @return callable(string,string,array<string,mixed>):array{0:int,1:array<string,string>,2:string}
 */
return static function (array $container): callable {
    /** @var list<array{method:string,pattern:string,handler:callable(array<string,mixed>,array<int,string>):array{0:int,1:array<string,string>,2:string}}> $routes */
    $routes = [
        ['method' => 'POST', 'pattern' => '#^/tag$#', 'handler' => static fn (array $norm): array => $container['tagController']()->create($norm)],
        ['method' => 'GET', 'pattern' => '#^/tag/([A-Za-z0-9]{26})$#', 'handler' => static fn (array $norm, array $m): array => $container['tagController']()->get($norm, $m[1])],
        ['method' => 'PATCH', 'pattern' => '#^/tag/([A-Za-z0-9]{26})$#', 'handler' => static fn (array $norm, array $m): array => $container['tagController']()->patch($norm, $m[1])],
        ['method' => 'DELETE', 'pattern' => '#^/tag/([A-Za-z0-9]{26})$#', 'handler' => static fn (array $norm, array $m): array => $container['tagController']()->delete($norm, $m[1])],

        ['method' => 'POST', 'pattern' => '#^/tag/([A-Za-z0-9]{26})/assign$#', 'handler' => static fn (array $norm, array $m): array => $container['assignController']()->assign($norm, $m[1])],
        ['method' => 'POST', 'pattern' => '#^/tag/([A-Za-z0-9]{26})/unassign$#', 'handler' => static fn (array $norm, array $m): array => $container['assignController']()->unassign($norm, $m[1])],
        ['method' => 'POST', 'pattern' => '#^/tag/assign-bulk$#', 'handler' => static fn (array $norm): array => $container['assignController']()->bulk($norm)],
        ['method' => 'POST', 'pattern' => '#^/tag/assignment/bulk$#', 'handler' => static fn (array $norm): array => $container['assignController']()->assignBulkToEntity($norm)],

        ['method' => 'GET', 'pattern' => '#^/tag/assignments$#', 'handler' => static fn (array $norm): array => $container['assignmentReadController']()->listByEntity($norm)],

        ['method' => 'GET', 'pattern' => '#^/tag/search$#', 'handler' => static fn (array $norm): array => $container['searchController']()->get($norm)],
        ['method' => 'GET', 'pattern' => '#^/tag/suggest$#', 'handler' => static fn (array $norm): array => $container['suggestController']()->get($norm)],

        ['method' => 'GET', 'pattern' => '#^/tag/([A-Za-z0-9]{26})/synonym$#', 'handler' => static fn (array $norm, array $m): array => $container['synonymController']()->list(['id' => $m[1]])],
        ['method' => 'POST', 'pattern' => '#^/tag/([A-Za-z0-9]{26})/synonym$#', 'handler' => static fn (array $norm, array $m): array => $container['synonymController']()->add(['id' => $m[1]], is_array($norm['body'] ?? null) ? $norm['body'] : [])],
        ['method' => 'DELETE', 'pattern' => '#^/tag/([A-Za-z0-9]{26})/synonym$#', 'handler' => static fn (array $norm, array $m): array => $container['synonymController']()->remove(['id' => $m[1]], is_array($norm['body'] ?? null) ? $norm['body'] : [])],

        ['method' => 'GET', 'pattern' => '#^/tag/redirect/([A-Za-z0-9]{26})$#', 'handler' => static fn (array $norm, array $m): array => $container['redirectController']()->resolve(['fromId' => $m[1]])],

        ['method' => 'GET', 'pattern' => '#^/tag/_status$#', 'handler' => static fn (): array => [
            200,
            ['Content-Type' => 'application/json'],
            json_encode($container['statusController']()->status(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{"code":"encode_error"}',
        ]],
    ];

    return static function (string $method, string $path, array $norm) use ($routes): array {
        foreach ($routes as $route) {
            if ($route['method'] !== $method || !preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            return $route['handler']($norm, $matches);
        }

        return [404, ['Content-Type' => 'application/json'], json_encode(['code' => 'not_found']) ?: '{"code":"not_found"}'];
    };
};
