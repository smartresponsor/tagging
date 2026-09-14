<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagBootstrapRuntimeCompositionTruthTest extends TestCase
{
    public function testPackageHostedCompositionRootDoesNotShipStandaloneAppSurface(): void
    {
        $root = dirname(__DIR__);

        foreach ([
            'config/services.yaml',
            'config/routes.yaml',
            'src/TaggingBundle.php',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }

        foreach ([
            'src/Kernel.php',
            'config/bootstrap.php',
            'config/bundles.php',
            'public/index.php',
            'bin/console',
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
            'services/infrastructure.yaml',
            'services/cache.yaml',
            'services/read_model.yaml',
            'services/application.yaml',
            'services/http.yaml',
            'services/ops.yaml',
            'services/core.yaml',
            'services/tagging.yaml',
        ] as $layer) {
            self::assertStringContainsString($layer, $services);
        }
    }

    public function testRuntimeTruthNamesHostedPackageAsActiveRuntime(): void
    {
        $root = dirname(__DIR__);
        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $readme = file_get_contents($root . '/README.md');

        self::assertSame('library', $composer['type'] ?? null);
        self::assertIsString($readme);
        self::assertStringContainsString('Package mode note', $readme);
        self::assertStringContainsString('does not ship its own Kernel', $readme);
        self::assertFileDoesNotExist($root . '/tag.yaml');
        self::assertDirectoryDoesNotExist($root . '/host-minimal');
    }
}
