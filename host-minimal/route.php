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
    $catalog = require dirname(__DIR__) . '/config/tag_route_catalog.php';
    $controller = static fn(string $id): object => $container[$id]();
    $runtimeVersion = static fn(): string => $container['runtime']()['version'] ?? 'dev';
    $routeDefinitions = is_array($catalog['routes'] ?? null) ? $catalog['routes'] : [];
    $buildHandler = static function (array $definition) use ($json, $controller, $runtimeVersion): callable {
        $serviceId = (string) ($definition['service_id'] ?? '');
        $action = (string) ($definition['action'] ?? '');
        $path = (string) ($definition['path'] ?? '');
        $responseHeader = (string) ($definition['response_header'] ?? '');
        if ('' === $serviceId || '' === $action) {
            throw new LogicException('invalid_route_catalog_entry');
        }

        if ('' !== $responseHeader) {
            return static fn(array $norm = [], array $matches = []): array => $json->respond(
                200,
                $controller($serviceId)->{$action}(),
                [
                    $responseHeader => $runtimeVersion(),
                    'Cache-Control' => 'no-store',
                ],
            );
        }

        if (str_contains($path, '{id}')) {
            return static fn(array $norm, array $matches): array => $controller($serviceId)->{$action}(
                $norm,
                $matches[1],
            );
        }

        return static fn(array $norm, array $matches = []): array => $controller($serviceId)->{$action}($norm);
    };

    $routes = array_values(array_filter(array_map(
        static function (mixed $definition) use ($buildHandler): ?array {
            if (!is_array($definition)) {
                return null;
            }

            $method = (string) ($definition['method'] ?? '');
            $pattern = (string) ($definition['pattern'] ?? '');
            if ('' === $method || '' === $pattern) {
                return null;
            }

            return [
                'method' => $method,
                'pattern' => $pattern,
                'handler' => $buildHandler($definition),
            ];
        },
        $routeDefinitions,
    )));

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
