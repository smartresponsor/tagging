<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class ReleaseGradePortraitAuditTest extends TestCase
{
    public function testReleaseGradePortraitAuditPasses(): void
    {
        $cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__).'/tools/audit/tag-release-grade-portrait-audit.php');
        exec($cmd.' 2>&1', $output, $code);

        self::assertSame(0, $code, implode(PHP_EOL, $output));
    }
}
