<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagBootstrapRuntimeCompositionTruthTest extends TestCase
{
    public function testDualRuntimeCompositionRootShipsStandaloneAndBundleSurfaces(): void
    {
        $root = dirname(__DIR__);

        foreach ([
            'config/services.yaml',
            'config/routes.yaml',
            'src/TaggingBundle.php',
            'src/Kernel.php',
            'config/bundles.php',
            'bin/console',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }

        foreach ([
            'config/bootstrap.php',
            'public/index.php',
            'host-minimal',
        ] as $path) {
            self::assertFileDoesNotExist($root . '/' . $path);
        }
    }

    public function testPackageHostedServiceCompositionImportsExpectedLayers(): void
    {
        $services = file_get_contents(dirname(__DIR__) . '/config/services.yaml');
        self::assertIsString($services);

        foreach ([
            'services/tag_infrastructure.yaml',
            'services/cache.yaml',
            'services/tag_read_model.yaml',
            'services/tag_application.yaml',
            'services/tag_http.yaml',
            'services/tag_ops.yaml',
            'services/tag_core.yaml',
            'services/tag_services.yaml',
        ] as $layer) {
            self::assertStringContainsString($layer, $services);
        }
    }

    public function testRuntimeTruthNamesDualModeAsActiveRuntime(): void
    {
        $root = dirname(__DIR__);
        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $readme = file_get_contents($root . '/README.md');

        self::assertSame('library', $composer['type'] ?? null);
        self::assertIsString($readme);
        self::assertStringContainsString('Dual-runtime mode', $readme);
        self::assertStringContainsString('standalone Symfony application', $readme);
        self::assertFileDoesNotExist($root . '/tag.yaml');
        self::assertDirectoryDoesNotExist($root . '/host-minimal');
    }
}
