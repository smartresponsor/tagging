<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagPostCanonTestRunnerWave21Test extends TestCase
{
    public function testPostCanonTestRunnerExistsAndKeepsNamespacePolicy(): void
    {
        $repoRoot = dirname(__DIR__);
        $runnerPath = $repoRoot . '/tools/test/tag-post-canon-tests-wave21.php';

        self::assertFileExists($runnerPath);

        $contents = (string) file_get_contents($runnerPath);

        self::assertStringContainsString('TagCanonMilestoneWave18AuditTest.php', $contents);
        self::assertStringContainsString('TagPostCanonVerificationWave20Test.php', $contents);
        self::assertStringContainsString('App\\\\Tagging\\\\', $contents);
    }
}
