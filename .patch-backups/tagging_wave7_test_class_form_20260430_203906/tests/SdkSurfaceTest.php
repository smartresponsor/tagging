<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SdkSurfaceTest extends TestCase
{
    public function testSdkMatchesPublicSurfaceOnly(): void
    {
        $php = (string) file_get_contents(dirname(__DIR__) . '/sdk/php/tag/Client.php');
        $ts = (string) file_get_contents(dirname(__DIR__) . '/sdk/ts/tag/client.ts');

        foreach (['/tag/_status', '/tag/_surface', '/tag/search', '/tag/suggest', '/tag/assignments', '/assign', '/unassign'] as $token) {
            self::assertStringContainsString($token, $php);
            self::assertStringContainsString($token, $ts);
        }

        foreach (['/synonym', '/redirect/', '/tag/assign-bulk', '/tag/assignment/bulk'] as $token) {
            self::assertStringNotContainsString($token, $php);
            self::assertStringNotContainsString($token, $ts);
        }
    }
}
