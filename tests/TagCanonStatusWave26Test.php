<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagCanonStatusWave26Test extends TestCase
{
    public function testMachineReadableCanonStatusAuditPasses(): void
    {
        $repoRoot = dirname(__DIR__);
        $auditPath = $repoRoot . '/tools/test/tag-canon-status-wave26.php';

        self::assertFileExists($auditPath);

        $command = PHP_BINARY . ' ' . escapeshellarg($auditPath);
        exec($command, $output, $exitCode);

        self::assertSame(
            0,
            $exitCode,
            implode(PHP_EOL, $output),
        );
    }
}
