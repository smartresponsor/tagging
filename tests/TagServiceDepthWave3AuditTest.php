<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagServiceDepthWave3AuditTest extends TestCase
{
    public function testServiceDepthAuditPasses(): void
    {
        $root = dirname(__DIR__);
        $script = $root . '/tools/audit/tag-service-depth-audit.php';

        self::assertFileExists($script);

        $command = PHP_BINARY . ' ' . escapeshellarg($script) . ' 2>&1';
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
    }
}
