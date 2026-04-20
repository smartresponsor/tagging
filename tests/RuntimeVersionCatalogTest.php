<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Http\Api\Tag\RuntimeSurfaceCatalog;
use App\Tagging\Http\Api\Tag\StatusController;
use PHPUnit\Framework\TestCase;

final class RuntimeVersionCatalogTest extends TestCase
{
    public function testStatusVersionCanMatchRuntimeCatalog(): void
    {
        $catalog = RuntimeSurfaceCatalog::read();
        $version = (string) ($catalog['version'] ?? '');

        $payload = (new StatusController(null, $version))->status();

        self::assertNotSame('', $version);
        self::assertSame($version, $payload['version']);
    }

    public function testRuntimeCatalogRoutesExposeStatusAndDiscovery(): void
    {
        $catalog = RuntimeSurfaceCatalog::read();
        self::assertSame('/tag/_status', $catalog['route']['status'] ?? null);
        self::assertSame('/tag/_surface', $catalog['route']['discovery'] ?? null);
    }
}
