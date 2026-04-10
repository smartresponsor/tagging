<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagPublicSurfaceConfigTest extends TestCase
{
    public function testRuntimeSurfaceIncludesBulkAssignmentRoutes(): void
    {
        $runtime = require __DIR__ . '/../config/tag_runtime.php';

        self::assertIsArray($runtime);
        self::assertSame('POST /tag/assignments/bulk', $runtime['route']['assignments_bulk'] ?? null);
        self::assertSame('POST /tag/assignments/bulk-to-entity', $runtime['route']['assignments_bulk_to_entity'] ?? null);

        $publicSurface = $runtime['public_surface'] ?? [];
        self::assertContains(
            ['method' => 'POST', 'path' => '/tag/assignments/bulk', 'name' => 'assignments bulk'],
            $publicSurface,
        );
        self::assertContains(
            ['method' => 'POST', 'path' => '/tag/assignments/bulk-to-entity', 'name' => 'assignments bulk to entity'],
            $publicSurface,
        );
    }
}
