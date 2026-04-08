<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * @return array{
 *     tenant:string,
 *     tags:array<int,array<string,mixed>>,
 *     links:array<int,array<string,mixed>>,
 *     catalog:array<string,mixed>
 * }
 */
return static function (string $root): array {
    $fixturePath = $root . '/fixtures/tag-demo-fixture.php';
    $catalogPath = $root . '/fixtures/tag-demo-catalog.php';

    $fixture = require $fixturePath;
    $catalog = require $catalogPath;

    if (!is_array($fixture) || !is_array($catalog)) {
        throw new RuntimeException('invalid_demo_fixture_payload');
    }

    return [
        'tenant' => (string) ($catalog['tenant'] ?? 'demo'),
        'tags' => is_array($fixture['tags'] ?? null) ? $fixture['tags'] : [],
        'links' => is_array($fixture['links'] ?? null) ? $fixture['links'] : [],
        'catalog' => $catalog,
    ];
};
