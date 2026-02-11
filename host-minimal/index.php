<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/** @var array<string, callable():mixed> $container */
$container = require __DIR__ . '/bootstrap.php';
/** @var callable(array<string, callable():mixed>):callable(string,string,array<string,mixed>):array{0:int,1:array<string,string>,2:string} $routerFactory */
$routerFactory = require __DIR__ . '/route.php';
$dispatch = $routerFactory($container);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$rawBody = file_get_contents('php://input') ?: '';
$norm = $container['idempotencyMiddleware']()->normalize($_SERVER, $_GET, $rawBody);
[$code, $headers, $body] = $dispatch($method, $path, $norm);

http_response_code($code);
foreach ($headers as $name => $value) {
    header($name . ': ' . $value);
}

echo $body;
