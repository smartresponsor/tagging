<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class PublicSurfaceConsistencyTest extends TestCase
{
    public function testSymfonyNativeRouteDoesNotExposeSynonymOrRedirect(): void
    {
        $route = (string) file_get_contents(__DIR__ . '/../config/routes/tagging_native.yaml');
        $catalog = (string) file_get_contents(__DIR__ . '/../tag.yaml');

        self::assertStringNotContainsString('/synonym', $route);
        self::assertStringNotContainsString('/redirect/', $route);
        self::assertStringNotContainsString('/tag/assign-bulk', $route);
        self::assertStringNotContainsString('/tag/assignment/bulk', $route);
        self::assertStringContainsString('/tag/assignments/bulk', $route);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $route);

        self::assertStringContainsString('/tag/_surface', $catalog);
        self::assertStringContainsString('X-Tag-Surface-Version', $catalog);
    }

    public function testSymfonyRouteConfigMatchesPublicSurface(): void
    {
        $route = (string) file_get_contents(__DIR__ . '/../config/routes/tagging_native.yaml');
        self::assertStringNotContainsString('/synonym', $route);
        self::assertStringNotContainsString('/redirect/', $route);
        self::assertStringNotContainsString('/tag/assign-bulk', $route);
        self::assertStringNotContainsString('/tag/assignment/bulk', $route);
        self::assertStringContainsString('/tag/_surface', $route);
        self::assertStringContainsString('App\Tagging\\Http\\Api\\Tag\\AssignController::assign', $route);
        self::assertStringContainsString('App\Tagging\\Http\\Api\\Tag\\SuggestController::get', $route);
    }
}
