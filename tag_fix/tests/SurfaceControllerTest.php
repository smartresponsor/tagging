<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\RuntimeVersion;
use App\Http\Api\Tag\SurfaceController;
use PHPUnit\Framework\TestCase;

final class SurfaceControllerTest extends TestCase
{
    public function testSurfaceContainsDiscoveryRoute(): void
    {
        $payload = (new SurfaceController())->surface();
        self::assertTrue($payload['ok']);
        self::assertSame(RuntimeVersion::read(), $payload['version']);
        self::assertSame('/tag/_surface', $payload['route']['discovery']);
    }
}
