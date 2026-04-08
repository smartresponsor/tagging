<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagAuditTruthScriptsTest extends TestCase
{
    public function testPublicRoutePathsAreProjectedFromCanonicalCatalog(): void
    {
        $projection = require __DIR__.'/../config/tag_public_route_paths.php';

        self::assertSame('/tag/assignments/bulk', $projection['operations']['assignments_bulk'] ?? null);
        self::assertSame('/tag/search', $projection['operations']['search'] ?? null);
        self::assertContains('/tag/assignments/bulk-to-entity', $projection['paths'] ?? []);
        self::assertNotContains('/tag/_webhooks', $projection['paths'] ?? []);
    }

    public function testContractAuditScriptUsesCanonicalPublicPaths(): void
    {
        $result = $this->runAudit('tag-contract-audit.php');

        self::assertSame(0, $result['exit']);
        self::assertStringContainsString('tag-contract-audit: ok', $result['stdout']);
    }

    public function testSurfaceAuditScriptUsesCanonicalPublicPaths(): void
    {
        $result = $this->runAudit('tag-surface-audit.php');

        self::assertSame(0, $result['exit']);
        self::assertStringContainsString('tag-surface-audit: ok', $result['stdout']);
    }

    /** @return array{exit:int,stdout:string,stderr:string} */
    private function runAudit(string $script): array
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../tools/audit/'.$script);
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
