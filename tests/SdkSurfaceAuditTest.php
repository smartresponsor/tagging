<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SdkSurfaceAuditTest extends TestCase
{
    public function testSdkTargetsPublicSurfaceOnly(): void
    {
        foreach ([
            __DIR__.'/../sdk/README.md',
            __DIR__.'/../sdk/php/tag/Client.php',
            __DIR__.'/../sdk/ts/tag/client.ts',
        ] as $file) {
            $text = (string) file_get_contents($file);
            self::assertStringContainsString('/tag/_surface', $text);
            self::assertStringContainsString('/tag/search', $text);
            self::assertStringContainsString('/tag/suggest', $text);
            self::assertStringNotContainsString('/tag/assign-bulk', $text);
            self::assertStringNotContainsString('/tag/assignment/bulk', $text);
            self::assertStringNotContainsString('/tag/redirect/', $text);
            self::assertStringNotContainsString('/synonym', $text);
            self::assertStringNotContainsString('/tag/facet', $text);
            self::assertStringNotContainsString('/tag/cloud', $text);
        }
    }
}
