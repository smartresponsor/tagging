<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagCiWorkflowTruthTest extends TestCase
{
    public function testWorkflowIncludesCurrentDeliveryAndReleaseAudits(): void
    {
        $workflow = file_get_contents(__DIR__ . '/../.github/workflows/ci.yml');

        self::assertIsString($workflow);
        self::assertStringContainsString('composer run -n audit:demo-truth-pack', $workflow);
        self::assertStringContainsString('composer run -n audit:release-grade-portrait', $workflow);
        self::assertStringContainsString('composer run -n smoke:runtime', $workflow);
        self::assertStringContainsString('uses: actions/upload-artifact@v4', $workflow);
    }

    public function testCiWorkflowAuditPassesAgainstCurrentWorkflow(): void
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/audit/tag-ci-workflow-audit.php');
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
        self::assertStringContainsString('CI workflow audit passed.', $stdout);
    }
}
