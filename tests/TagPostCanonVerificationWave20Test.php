<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagPostCanonVerificationWave20Test extends TestCase
{
    public function testPostCanonVerificationRunnerExists(): void
    {
        $repoRoot = dirname(__DIR__);
        $runnerPath = $repoRoot . '/tools/audit/tag-post-canon-verification-wave20.php';

        self::assertFileExists($runnerPath);

        $contents = (string) file_get_contents($runnerPath);

        self::assertStringContainsString('tag-canon-milestone-wave18-audit.php', $contents);
        self::assertStringContainsString('tag-canonicalization-review-wave19-audit.php', $contents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $contents);
    }
}
