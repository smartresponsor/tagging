<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$publicRoutePaths = require $root . '/config/tag_public_route_paths.php';
$surface = require $root . '/config/tag_public_surface.php';
$openapi = file_get_contents($root . '/contracts/http/tag-openapi.yaml');

$expectedOperations = array_keys(is_array($publicRoutePaths['operations'] ?? null) ? $publicRoutePaths['operations'] : []);
$expectedPaths = is_array($publicRoutePaths['paths'] ?? null) ? $publicRoutePaths['paths'] : [];
$surfaceRouteMap = is_array($surface['route'] ?? null) ? $surface['route'] : [];
$surfaceOperations = array_keys($surfaceRouteMap);
$surfacePaths = [];
foreach ($surfaceRouteMap as $route) {
    if (!is_string($route) || '' === $route) {
        continue;
    }

    $path = $route;
    if (1 === preg_match('/^[A-Z]+\s+(.+)$/', $route, $m)) {
        $path = $m[1];
    }
    $surfacePaths[] = $path;
}
$surfacePaths = array_values(array_unique($surfacePaths));

preg_match_all('/^  (\/tag[^:]*):$/m', (string) $openapi, $m);
$contractPaths = array_values(array_unique($m[1] ?? []));

sort($expectedOperations);
sort($surfaceOperations);
sort($expectedPaths);
sort($surfacePaths);
sort($contractPaths);

$errors = [];
if ($surfaceOperations !== $expectedOperations) {
    $errors['operations'] = [
        'expected' => $expectedOperations,
        'actual' => $surfaceOperations,
        'missing' => array_values(array_diff($expectedOperations, $surfaceOperations)),
        'extra' => array_values(array_diff($surfaceOperations, $expectedOperations)),
    ];
}
if ($surfacePaths !== $expectedPaths) {
    $errors['surface_paths'] = [
        'expected' => $expectedPaths,
        'actual' => $surfacePaths,
        'missing' => array_values(array_diff($expectedPaths, $surfacePaths)),
        'extra' => array_values(array_diff($surfacePaths, $expectedPaths)),
    ];
}
if ($contractPaths !== $expectedPaths) {
    $errors['contract_paths'] = [
        'expected' => $expectedPaths,
        'actual' => $contractPaths,
        'missing' => array_values(array_diff($expectedPaths, $contractPaths)),
        'extra' => array_values(array_diff($contractPaths, $expectedPaths)),
    ];
}

if ($errors !== []) {
    fwrite(STDERR, 'surface truth mismatch' . PHP_EOL . json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}
echo "tag-surface-audit: ok\n";
