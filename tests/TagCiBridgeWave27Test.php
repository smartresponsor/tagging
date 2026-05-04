<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagCiBridgeWave27Test extends TestCase
{
    public function testCiBridgeRunnerExistsAndChainsCanonHealthAndFullRunner(): void
    {
        $repoRoot = dirname(__DIR__);
        $runnerPath = $repoRoot . '/tools/test/tag-ci-bridge-wave27.php';

        self::assertFileExists($runnerPath);

        $contents = (string) file_get_contents($runnerPath);

        self::assertStringContainsString('tag-canon-status-wave26.php', $contents);
        self::assertStringContainsString('tag-post-canon-health-wave24.php', $contents);
        self::assertStringContainsString('tag-post-canon-all-wave22.php', $contents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $contents);
    }
}
