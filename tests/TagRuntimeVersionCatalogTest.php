<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Provider\Runtime\TagRuntimeSurfaceCatalog;
use App\Tagging\Service\Http\Tag\TagStatusService;
use PHPUnit\Framework\TestCase;

final class TagRuntimeVersionCatalogTest extends TestCase
{
    public function testStatusServiceMatchesTheCurrentZeroControllerSurface(): void
    {
        $catalog = TagRuntimeSurfaceCatalog::read();
        $response = (new TagStatusService())();
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertNotSame('', (string) ($catalog['version'] ?? ''));
        self::assertSame('tagging', $payload['service'] ?? null);
        self::assertSame('zero-controller', $payload['surface'] ?? null);
    }

    public function testRuntimeCatalogRoutesExposeStatusAndDiscovery(): void
    {
        $catalog = TagRuntimeSurfaceCatalog::read();
        self::assertSame('/tag/_status', $catalog['route']['status'] ?? null);
        self::assertSame('/tag/_surface', $catalog['route']['discovery'] ?? null);
    }
}
