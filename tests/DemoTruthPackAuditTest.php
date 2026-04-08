<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class DemoTruthPackAuditTest extends TestCase
{
    public function testDemoTruthPackAuditPasses(): void
    {
        $cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__).'/tools/audit/tag-demo-truth-pack-audit.php');
        exec($cmd.' 2>&1', $output, $code);

        self::assertSame(0, $code, implode(PHP_EOL, $output));
    }
}
