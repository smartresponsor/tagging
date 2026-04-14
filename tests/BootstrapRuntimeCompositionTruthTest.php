<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class BootstrapRuntimeCompositionTruthTest extends TestCase
{
    public function testSymfonyNativeCompositionRootFilesExist(): void
    {
        $root = dirname(__DIR__);

        foreach ([
            'src/Kernel.php',
            'config/bootstrap.php',
            'config/bundles.php',
            'config/services.yaml',
            'config/routes.yaml',
            'public/index.php',
            'bin/console',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }
    }

    public function testSymfonyNativeServiceCompositionImportsExpectedLayers(): void
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

    public function testSymfonyNativeRuntimeTruthNoLongerNamesHostMinimalAsActiveRuntime(): void
    {
        $tagYaml = file_get_contents(dirname(__DIR__) . '/tag.yaml');
        self::assertIsString($tagYaml);

        self::assertStringContainsString('runtime: symfony-native', $tagYaml);
        self::assertStringNotContainsString('runtime: host-minimal', $tagYaml);
    }
}
