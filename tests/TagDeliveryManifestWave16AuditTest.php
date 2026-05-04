<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagDeliveryManifestWave16AuditTest extends TestCase
{
    public function testDeliveryManifestAuditPasses(): void
    {
        $repoRoot = dirname(__DIR__);
        $auditPath = $repoRoot . '/tools/audit/tag-delivery-manifest-wave16-audit.php';

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
