<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagPublicSurfaceConsistencyTest extends TestCase
{
    public function testCrudingOwnsGenericRoutesAfterLocalProjectionRetirement(): void
    {
        $root = dirname(__DIR__);
        $route = (string) file_get_contents($root . '/config/routes.yaml');
        $openApi = (string) file_get_contents($root . '/contracts/http/tag-openapi.yaml');

        self::assertStringContainsString('Cruding bundle', $route);
        self::assertStringNotContainsString('tagging_native', $route);
        self::assertFileDoesNotExist($root . '/config/routes/tagging_native.yaml');
        self::assertFileDoesNotExist($root . '/tag.yaml');
        self::assertStringNotContainsString('/synonym', $openApi);
        self::assertStringNotContainsString('/redirect/', $openApi);
        self::assertStringContainsString('/tag/assignments/bulk:', $openApi);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity:', $openApi);
    }

    public function testOpenApiMatchesThePublicReadAndDiscoverySurface(): void
    {
        $openApi = (string) file_get_contents(dirname(__DIR__) . '/contracts/http/tag-openapi.yaml');

        self::assertStringContainsString('/tag/_surface:', $openApi);
        self::assertStringContainsString('/tag/search:', $openApi);
        self::assertStringContainsString('/tag/suggest:', $openApi);
        self::assertStringNotContainsString('/tag/assign-bulk:', $openApi);
        self::assertStringNotContainsString('/tag/assignment/bulk:', $openApi);
    }
}
