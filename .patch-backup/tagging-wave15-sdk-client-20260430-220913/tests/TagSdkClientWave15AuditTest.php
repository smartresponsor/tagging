<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagSdkClientWave15AuditTest extends TestCase
{
    public function testSdkClientAuditPasses(): void
    {
        $repoRoot = dirname(__DIR__);
        $auditPath = $repoRoot . '/tools/audit/tag-sdk-client-wave15-audit.php';

        self::assertFileExists($auditPath);

        $command = PHP_BINARY . ' ' . escapeshellarg($auditPath);
        exec($command, $output, $exitCode);

        self::assertSame(
            0,
            $exitCode,
            implode(PHP_EOL, $output)
        );
    }
}
