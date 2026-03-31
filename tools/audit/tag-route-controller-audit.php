<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$catalog = require $root . '/config/tag_route_catalog.php';
$routes = is_array($catalog['routes'] ?? null) ? $catalog['routes'] : [];
$errors = [];
foreach ($routes as $route) {
    if (!is_array($route)) {
        continue;
    }

    $controller = trim((string) ($route['controller'] ?? ''));
    $operation = trim((string) ($route['operation'] ?? 'unknown'));
    if ('' === $controller) {
        $errors[] = 'missing controller for operation ' . $operation;

        continue;
    }

    [$class, $method] = explode('::', $controller, 2);
    $path = $root . '/src/' . str_replace('App\\', '', $class);
    $path = str_replace('\\', '/', $path) . '.php';
    if (!is_file($path)) {
        $errors[] = 'missing file ' . $path;
        continue;
    }
    require_once $path;
    if (!class_exists($class)) {
        $errors[] = 'missing class ' . $class;
        continue;
    }
    if (!method_exists($class, $method)) {
        $errors[] = 'missing method ' . $controller;
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-route-controller-audit: ok\n";
