<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class CliCommandLayerTest extends TestCase
{
    public function testCliHelpListsExpectedCommands(): void
    {
        $result = $this->runCli('help --pretty');

        self::assertSame(0, $result['exit']);
        self::assertStringContainsString('"commands"', $result['stdout']);
        self::assertStringContainsString('"status"', $result['stdout']);
        self::assertStringContainsString('"surface"', $result['stdout']);
        self::assertStringContainsString('"assignments', $result['stdout']);
    }

    public function testCliStatusReturnsServicePayload(): void
    {
        $result = $this->runCli('status --pretty');

        self::assertSame(0, $result['exit']);
        self::assertStringContainsString('"service": "tag"', $result['stdout']);
        self::assertStringContainsString('"version":', $result['stdout']);
    }

    public function testCliSurfaceReturnsPublicSurface(): void
    {
        $result = $this->runCli('surface --pretty');

        self::assertSame(0, $result['exit']);
        self::assertStringContainsString('"public_surface"', $result['stdout']);
        self::assertStringContainsString('/tag/_surface', $result['stdout']);
        self::assertStringContainsString('/tag/assignments/bulk', $result['stdout']);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $result['stdout']);
    }

    public function testCliRejectsUnknownCommand(): void
    {
        $result = $this->runCli('unknown-command');

        self::assertSame(2, $result['exit']);
        self::assertStringContainsString('"code": "invalid_cli_arguments"', $result['stderr']);
        self::assertStringContainsString('Unknown command: unknown-command', $result['stderr']);
    }

    /** @return array{exit:int,stdout:string,stderr:string} */
    private function runCli(string $args): array
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/cli/tag.php') . ' ' . $args;
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

        return ['exit' => $exit, 'stdout' => $stdout, 'stderr' => $stderr];
    }
}
