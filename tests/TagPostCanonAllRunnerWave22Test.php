<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagPostCanonAllRunnerWave22Test extends TestCase
{
    public function testCompletePostCanonRunnerExistsAndChainsWave20AndWave21(): void
    {
        $repoRoot = dirname(__DIR__);
        $runnerPath = $repoRoot . '/tools/test/tag-post-canon-all-wave22.php';

        self::assertFileExists($runnerPath);

        $contents = (string) file_get_contents($runnerPath);

        self::assertStringContainsString('tag-post-canon-verification-wave20.php', $contents);
        self::assertStringContainsString('tag-post-canon-tests-wave21.php', $contents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $contents);
    }
}
