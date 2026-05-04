<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagLegacyDuplicateSurfaceWave5AuditTest extends TestCase
{
    public function testLegacyDuplicateSurfaceAuditPasses(): void
    {
        $root = dirname(__DIR__);
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/tools/audit/tag-legacy-duplicate-surface-audit.php');
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
    }
}
