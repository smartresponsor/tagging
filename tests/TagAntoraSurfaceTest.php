<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagAntoraSurfaceTest extends TestCase
{
    public function testAntoraProducerSurfaceExistsAndSeparatesDocRoles(): void
    {
        $antora = file_get_contents(__DIR__ . '/../docs/antora.yml');
        $index = file_get_contents(__DIR__ . '/../docs/modules/ROOT/pages/index.adoc');
        $api = file_get_contents(__DIR__ . '/../docs/modules/ROOT/pages/api.adoc');

        self::assertIsString($antora);
        self::assertIsString($index);
        self::assertIsString($api);

        self::assertStringContainsString('start_page: ROOT:index.adoc', $antora);
        self::assertStringContainsString('GitHub-facing repository docs', $index);
        self::assertStringContainsString('Hand-written narrative docs', $index);
        self::assertStringContainsString('Generated or reference surfaces', $index);
        self::assertStringContainsString('contracts/http/tag-openapi.yaml', $api);
        self::assertStringContainsString('public/tag/openapi/', $api);
        self::assertStringContainsString('no phpDocumentor / Doctum code-reference surface is shipped here currently', $api);
    }

    public function testAntoraSurfaceAuditPasses(): void
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/audit/tag-antora-surface-audit.php');
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
        self::assertStringContainsString('tag-antora-surface-audit: ok', $stdout);
    }
}
