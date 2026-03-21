<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class PublicSurfaceConsistencyTest extends TestCase
{
    public function testHostMinimalRouteDoesNotExposeSynonymOrRedirect(): void
    {
        $route = (string) file_get_contents(__DIR__.'/../host-minimal/route.php');
        self::assertStringNotContainsString('/synonym', $route);
        self::assertStringNotContainsString('/redirect/', $route);
        self::assertStringNotContainsString('/tag/assign-bulk', $route);
        self::assertStringNotContainsString('/tag/assignment/bulk', $route);
        self::assertStringContainsString('/tag/_surface', $route);
        self::assertStringContainsString('X-Tag-Surface-Version', $route);
        self::assertStringContainsString('Cache-Control', $route);
    }

    public function testSymfonyRouteConfigMatchesPublicSurface(): void
    {
        $route = (string) file_get_contents(__DIR__.'/../config/routes/tag.yaml');
        self::assertStringNotContainsString('/synonym', $route);
        self::assertStringNotContainsString('/redirect/', $route);
        self::assertStringNotContainsString('/tag/assign-bulk', $route);
        self::assertStringNotContainsString('/tag/assignment/bulk', $route);
        self::assertStringContainsString('/tag/_surface', $route);
        self::assertStringContainsString('App\\Http\\Api\\Tag\\AssignController::assign', $route);
        self::assertStringContainsString('App\\Http\\Api\\Tag\\SuggestController::get', $route);
    }
}
