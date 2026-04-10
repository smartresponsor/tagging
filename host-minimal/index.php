<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

require_once __DIR__ . '/cors.php';

/** @var array<string, callable():mixed> $container */
$container = require __DIR__ . '/bootstrap.php';
/**
 * @var callable(array<string, callable():mixed>): callable $routerFactory (
 *     string,
 *     string,
 *     array<string, mixed>
 * ): array{0:int,1:array<string,string>,2:string} $routerFactory
 */
$routerFactory = require __DIR__ . '/route.php';
$dispatch = $routerFactory($container);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$corsHeaders = tag_cors_headers(
    isset($_SERVER['HTTP_ORIGIN']) ? (string) $_SERVER['HTTP_ORIGIN'] : null,
    getenv('TAG_ALLOW_ORIGIN') !== false ? (string) getenv('TAG_ALLOW_ORIGIN') : null,
);

if ($method === 'OPTIONS') {
    http_response_code(204);
    foreach ($corsHeaders as $name => $value) {
        header($name . ': ' . $value);
    }
    exit;
}

$rawBody = file_get_contents('php://input') ?: '';
$norm = $container['idempotencyMiddleware']()->normalize($_SERVER, $_GET, $rawBody);
$pipeline = $container['httpPipeline']();
[$code, $headers, $body] = $pipeline->handle(
    ['method' => $method, 'path' => $path] + $norm,
    static fn(array $request): array => $dispatch($method, $path, $request),
);
$headers = $headers + $corsHeaders;

http_response_code((int) $code);
foreach ($headers as $name => $value) {
    header($name . ': ' . $value);
}

echo $body;
