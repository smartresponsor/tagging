<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagCiBridgeCommandWrapperWave32Test extends TestCase
{
    public function testPowerShellWrapperDoesNotDependOnNonEmptyPSScriptRootOnly(): void
    {
        $repoRoot = dirname(__DIR__);
        $wrapperPath = $repoRoot . '/tools/test/tag-ci-bridge-wave28.ps1';

        self::assertFileExists($wrapperPath);

        $contents = (string) file_get_contents($wrapperPath);

        self::assertStringContainsString('[string]$RepoRoot', $contents);
        self::assertStringContainsString('[string]::IsNullOrWhiteSpace($PSScriptRoot)', $contents);
        self::assertStringContainsString('(Get-Location).Path', $contents);
        self::assertStringContainsString('tag-ci-bridge-wave27.php', $contents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $contents);
    }
}
