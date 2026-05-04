<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeSurfaceTest extends TestCase
{
    public function testPublicRuntimeTruthIsHostedPackage(): void
    {
        $tagYaml = file_get_contents(dirname(__DIR__) . '/tag.yaml');
        self::assertIsString($tagYaml);
        self::assertStringContainsString('runtime: hosted-package', $tagYaml);
        self::assertStringNotContainsString('runtime: symfony-native', $tagYaml);
        self::assertStringNotContainsString('runtime: host-minimal', $tagYaml);
    }

    public function testStandaloneFrontControllerIsNotPartOfPackageSurface(): void
    {
        self::assertFileDoesNotExist(dirname(__DIR__) . '/public/index.php');
        self::assertFileDoesNotExist(dirname(__DIR__) . '/bin/console');
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
