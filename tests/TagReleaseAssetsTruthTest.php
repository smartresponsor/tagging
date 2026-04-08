<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagReleaseAssetsTruthTest extends TestCase
{
    public function testReleaseAssetsExistAndReferenceCurrentRcFlow(): void
    {
        $changelog = file_get_contents(__DIR__ . '/../CHANGELOG.md');
        $notes = file_get_contents(__DIR__ . '/../RELEASE_NOTES.md');
        $publicIndex = file_get_contents(__DIR__ . '/../docs/public/index.md');
        $checklist = file_get_contents(__DIR__ . '/../docs/release/rc-checklist.md');
        $workflow = file_get_contents(__DIR__ . '/../.github/workflows/release-rc.yml');

        self::assertIsString($changelog);
        self::assertIsString($notes);
        self::assertIsString($publicIndex);
        self::assertIsString($checklist);
        self::assertIsString($workflow);

        self::assertStringContainsString('0.2.8-rc1', $changelog);
        self::assertStringContainsString('prerelease / RC candidate', $notes);
        self::assertStringContainsString('docs/ops/runbook.md', $publicIndex);
        self::assertStringContainsString('RC candidate', $checklist);
        self::assertStringContainsString('v0.2.8-rc1', $checklist);
        self::assertStringContainsString('audit:release-assets', $checklist);
        self::assertStringContainsString('composer run -n audit:release-assets', $workflow);
    }

    public function testReleaseAssetsAuditPasses(): void
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/audit/tag-release-assets-audit.php');
        $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($command, $descriptorSpec, $pipes, dirname(__DIR__));
        self::assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[2]);
        $exit = proc_close($process);

        self::assertSame(0, $exit, $stderr);
        self::assertStringContainsString('tag-release-assets-audit: ok', $stdout);
    }
}
