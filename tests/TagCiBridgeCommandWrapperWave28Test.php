<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagCiBridgeCommandWrapperWave28Test extends TestCase
{
    public function testCiBridgeCommandWrappersExistAndCallWave27Runner(): void
    {
        $repoRoot = dirname(__DIR__);
        $psWrapper = $repoRoot . '/tools/test/tag-ci-bridge-wave28.ps1';
        $shWrapper = $repoRoot . '/tools/test/tag-ci-bridge-wave28.sh';

        self::assertFileExists($psWrapper);
        self::assertFileExists($shWrapper);

        $psContents = (string) file_get_contents($psWrapper);
        $shContents = (string) file_get_contents($shWrapper);

        self::assertStringContainsString('tag-ci-bridge-wave27.php', $psContents);
        self::assertStringContainsString('tag-ci-bridge-wave27.php', $shContents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $psContents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $shContents);
    }
}
