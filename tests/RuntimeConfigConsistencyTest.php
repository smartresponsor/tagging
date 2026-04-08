<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RuntimeConfigConsistencyTest extends TestCase
{
    public function testRuntimeConfigIsDerivedFromPublicSurfaceConfig(): void
    {
        $runtime = require __DIR__.'/../config/tag_runtime.php';
        $surface = require __DIR__.'/../config/tag_public_surface.php';

        self::assertSame($surface['service'], $runtime['service']);
        self::assertSame($surface['version'], $runtime['version']);
        self::assertSame($surface['route']['status'] ?? null, $runtime['route']['status'] ?? null);
        self::assertSame($surface['doc']['sdk'] ?? null, $runtime['doc']['sdk'] ?? null);
        self::assertContains(
            ['method' => 'GET', 'path' => '/tag/_surface', 'name' => 'discovery'],
            $runtime['public_surface'],
        );
    }
}
