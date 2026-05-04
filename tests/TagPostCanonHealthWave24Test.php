<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagPostCanonHealthWave24Test extends TestCase
{
    public function testPostCanonHealthCheckExistsAndCoversVerificationSurface(): void
    {
        $repoRoot = dirname(__DIR__);
        $healthPath = $repoRoot . '/tools/test/tag-post-canon-health-wave24.php';
        self::assertFileExists($healthPath);
        $contents = (string) file_get_contents($healthPath);
        self::assertStringContainsString('tag-post-canon-verification-wave20.php', $contents);
        self::assertStringContainsString('tag-post-canon-tests-wave21.php', $contents);
        self::assertStringContainsString('tag-post-canon-all-wave22.php', $contents);
        self::assertStringContainsString('tag-post-canon-all-wave23.ps1', $contents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $contents);
    }
}
