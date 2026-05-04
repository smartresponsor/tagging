<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagPostCanonCommandWrapperWave23Test extends TestCase
{
    public function testPostCanonCommandWrappersExistAndCallWave22Runner(): void
    {
        $repoRoot = dirname(__DIR__);

        $psWrapper = $repoRoot . '/tools/test/tag-post-canon-all-wave23.ps1';
        $shWrapper = $repoRoot . '/tools/test/tag-post-canon-all-wave23.sh';

        self::assertFileExists($psWrapper);
        self::assertFileExists($shWrapper);

        $psContents = (string) file_get_contents($psWrapper);
        $shContents = (string) file_get_contents($shWrapper);

        self::assertStringContainsString('tag-post-canon-all-wave22.php', $psContents);
        self::assertStringContainsString('tag-post-canon-all-wave22.php', $shContents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $psContents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $shContents);
    }
}
