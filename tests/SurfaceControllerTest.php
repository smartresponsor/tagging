<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\RuntimeVersion;
use App\Http\Api\Tag\SurfaceController;
use PHPUnit\Framework\TestCase;

final class SurfaceControllerTest extends TestCase
{
    public function testSurfaceContainsMinimalRoutesAndDocs(): void
    {
        $payload = (new SurfaceController())->surface();
        self::assertTrue($payload['ok']);
        self::assertSame('symfony-native', $payload['runtime']);
        self::assertSame('/tag/_surface', $payload['surface']['discovery']);
        self::assertArrayHasKey('tour', $payload['examples']);
        self::assertArrayHasKey('admin', $payload['docs']);
        self::assertSame(RuntimeVersion::read(), $payload['version']);
    }
}
