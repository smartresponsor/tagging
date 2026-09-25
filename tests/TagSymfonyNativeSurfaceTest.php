<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagSymfonyNativeSurfaceTest extends TestCase
{
    public function testPublicRuntimeTruthIsDualMode(): void
    {
        $runtime = require dirname(__DIR__) . '/config/tag_runtime.php';
        self::assertIsArray($runtime);
        self::assertSame('dual-mode', $runtime['runtime'] ?? null);
    }

    public function testStandaloneConsoleAndKernelArePartOfComponentSurface(): void
    {
        self::assertFileDoesNotExist(dirname(__DIR__) . '/public/index.php');
        self::assertFileExists(dirname(__DIR__) . '/bin/console');
        self::assertFileExists(dirname(__DIR__) . '/src/Kernel.php');
        self::assertFileExists(dirname(__DIR__) . '/config/bundles.php');
        self::assertFileDoesNotExist(dirname(__DIR__) . '/config/bootstrap.php');
    }

    public function testContractStillDocumentsStatusAndSurfaceRoutes(): void
    {
        $contract = file_get_contents(dirname(__DIR__) . '/contracts/http/tag-openapi.yaml');
        self::assertIsString($contract);
        self::assertStringContainsString('/tag/_status', $contract);
        self::assertStringContainsString('/tag/_surface', $contract);
        self::assertStringNotContainsString('/tag/{id}/synonym', $contract);
        self::assertStringNotContainsString('/tag/redirect/{fromId}', $contract);
    }
}
