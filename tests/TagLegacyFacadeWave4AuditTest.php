<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagLegacyFacadeWave4AuditTest extends TestCase
{
    public function testLegacyFacadeAuditPasses(): void
    {
        $root = dirname(__DIR__);
        $cmd = PHP_BINARY . ' ' . escapeshellarg($root . '/tools/audit/tag-legacy-facade-audit.php');
        exec($cmd . ' 2>&1', $output, $status);

        self::assertSame(0, $status, implode(PHP_EOL, $output));
    }
}
