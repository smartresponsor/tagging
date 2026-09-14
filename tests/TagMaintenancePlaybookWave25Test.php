<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagMaintenancePlaybookWave25Test extends TestCase
{
    public function testMaintenancePlaybookAuditPasses(): void
    {
        $repoRoot = dirname(__DIR__);
        $auditPath = $repoRoot . '/tools/test/tag-maintenance-playbook-wave25.php';

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
