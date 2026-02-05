<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/** @var array<string, callable():mixed> $container */
$container = require __DIR__.'/bootstrap.php';
/** @var callable(array<string, callable():mixed>):array<int,array{method:string,pattern:string,handler:callable}> $routeFactory */
$routeFactory = require __DIR__.'/route.php';
$routes = $routeFactory($container);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$rawBody = file_get_contents('php://input') ?: '';
$norm = $container['idempotencyMiddleware']()->normalize($_SERVER, $_GET, $rawBody);

/** @param array{0:int,1:array<string,string>,2:string} $response */
$send = static function (array $response): never {
    [$code, $headers, $body] = $response;
    http_response_code($code);
    foreach ($headers as $name => $value) {
        header($name.': '.$value);
    }
    echo $body;
    exit;
};

foreach ($routes as $route) {
    if ($route['method'] !== $method || !preg_match($route['pattern'], $path, $matches)) {
        continue;
    }

    $send($route['handler']($norm, $matches));
}

$send([404, ['Content-Type' => 'application/json'], json_encode(['code' => 'not_found']) ?: '{"code":"not_found"}']);
