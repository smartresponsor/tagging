<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagCanonicalizationReviewWave19AuditTest extends TestCase
{
    public function testCanonicalizationReviewReportAuditPasses(): void
    {
        $repoRoot = dirname(__DIR__);
        $auditPath = $repoRoot . '/tools/audit/tag-canonicalization-review-wave19-audit.php';

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
