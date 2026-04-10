<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagLegacyArtifactRetirementTest extends TestCase
{
    public function testLegacyJsonFixtureIsRemoved(): void
    {
        self::assertFileDoesNotExist(__DIR__ . '/../fixtures/tag-demo.json');
    }

    public function testRepoHygieneDocsDescribeRetiredLegacyFixture(): void
    {
        $doc = file_get_contents(__DIR__ . '/../docs/ops/repo-hygiene.md');

        self::assertIsString($doc);
        self::assertStringContainsString('fixtures/tag-demo.json', $doc);
        self::assertStringContainsString('retired parallel demo truth artifacts', $doc);
    }

    public function testRepoHygieneAuditPassesWithoutLegacyFixture(): void
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/audit/tag-repo-hygiene-audit.php');
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptorSpec, $pipes, dirname(__DIR__));
        self::assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[2]);
        $exit = proc_close($process);

        self::assertSame(0, $exit, $stderr);
        self::assertStringContainsString('[repo-hygiene] OK', $stdout);
    }
}
