<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SdkSurfaceTest extends TestCase
{
    public function testSdkMatchesRunnableSurface(): void
    {
        $phpSdk = (string) file_get_contents(dirname(__DIR__) . '/sdk/php/tag/Client.php');
        $tsSdk = (string) file_get_contents(dirname(__DIR__) . '/sdk/ts/tag/client.ts');

        self::assertStringContainsString('/tag/_status', $phpSdk);
        self::assertStringContainsString('/tag/_surface', $phpSdk);
        self::assertStringContainsString('/tag/_status', $tsSdk);
        self::assertStringContainsString('/tag/_surface', $tsSdk);
        self::assertStringNotContainsString('/synonym', $phpSdk);
        self::assertStringNotContainsString('/redirect/', $tsSdk);
    }
}
