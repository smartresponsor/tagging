<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class BootstrapRuntimeCompositionTruthTest extends TestCase
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
        $tagYaml = file_get_contents(dirname(__DIR__) . '/tag.yaml');
        self::assertIsString($tagYaml);

        self::assertStringContainsString('runtime: hosted-package', $tagYaml);
        self::assertStringNotContainsString('runtime: symfony-native', $tagYaml);
        self::assertStringNotContainsString('runtime: host-minimal', $tagYaml);
    }
}
