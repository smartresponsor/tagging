<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRuntimeConfigConsistencyTest extends TestCase
{
    public function testRuntimeConfigIsTheSinglePublicSurfaceSource(): void
    {
        $runtime = require __DIR__ . '/../config/tag_runtime.php';

        self::assertSame('tag', $runtime['service'] ?? null);
        self::assertNotSame('', $runtime['version'] ?? '');
        self::assertSame('/tag/_status', $runtime['route']['status'] ?? null);
        self::assertSame('contracts/http/tag-openapi.yaml', $runtime['doc']['openapi'] ?? null);
        self::assertContains(
            ['method' => 'GET', 'path' => '/tag/_surface', 'nameEntity' => 'discovery'],
            $runtime['public_surface'] ?? [],
        );
        self::assertFileDoesNotExist(__DIR__ . '/../config/tag_public_surface.php');
    }
}
