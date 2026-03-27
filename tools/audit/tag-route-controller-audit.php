<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$yaml = file($root . '/tag.yaml', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
$errors = [];
foreach ($yaml as $line) {
    if (!str_contains($line, 'controller: ')) {
        continue;
    }
    $controller = trim(substr($line, strpos($line, 'controller: ') + 12));
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
