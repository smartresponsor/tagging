<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagCanonicalizationCompleteWave30Test extends TestCase
{
    public function testCanonicalizationCompleteAuditPasses(): void
    {
        $repoRoot = dirname(__DIR__);
        $auditPath = $repoRoot . '/tools/test/tag-canonicalization-complete-wave30.php';

        self::assertFileExists($auditPath);

        $command = PHP_BINARY . ' ' . escapeshellarg($auditPath);
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
    }
}
