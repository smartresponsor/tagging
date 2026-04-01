<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagReleasePreflightTest extends TestCase
{
    public function testPreflightIncludesCurrentCanonicalAudits(): void
    {
        $script = file_get_contents(__DIR__ . '/../tools/release/tag-preflight.php');

        self::assertIsString($script);
        self::assertStringContainsString('tag-surface-audit.php', $script);
        self::assertStringContainsString('tag-contract-audit.php', $script);
        self::assertStringContainsString('tag-route-controller-audit.php', $script);
        self::assertStringContainsString('tag-bootstrap-audit.php', $script);
        self::assertStringContainsString('tag-bootstrap-runtime-audit.php', $script);
        self::assertStringContainsString('tag-config-audit.php', $script);
        self::assertStringContainsString('tag-sdk-audit.php', $script);
        self::assertStringContainsString('tag-demo-truth-pack-audit.php', $script);
        self::assertStringContainsString('tag-release-grade-portrait-audit.php', $script);
        self::assertStringContainsString('tag-version-audit.php', $script);
        self::assertStringContainsString('tag-fixture-validate.php', $script);
    }

    public function testPreflightRunsSuccessfullyWithCurrentRepoState(): void
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/release/tag-preflight.php');
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
        self::assertStringContainsString('tag-preflight: ok', $stdout);
    }
}
