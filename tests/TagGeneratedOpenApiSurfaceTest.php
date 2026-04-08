<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagGeneratedOpenApiSurfaceTest extends TestCase
{
    public function testGeneratedSwaggerSurfaceExistsAndPointsToPublishedOpenApiArtifact(): void
    {
        $index = file_get_contents(__DIR__.'/../public/tag/openapi/index.html');
        $generated = file_get_contents(__DIR__.'/../public/tag/openapi/tag-openapi.yaml');
        $source = file_get_contents(__DIR__.'/../contracts/http/tag-openapi.yaml');

        self::assertIsString($index);
        self::assertIsString($generated);
        self::assertIsString($source);
        self::assertStringContainsString('./tag-openapi.yaml', $index);
        self::assertStringContainsString('SwaggerUIBundle', $index);
        self::assertSame($source, $generated);
    }

    public function testGeneratedOpenApiSurfaceAuditPasses(): void
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../tools/audit/tag-generated-openapi-surface-audit.php');
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
        self::assertStringContainsString('tag-generated-openapi-surface-audit: ok', $stdout);
    }
}
