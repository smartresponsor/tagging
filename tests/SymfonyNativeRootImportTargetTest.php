<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeRootImportTargetTest extends TestCase
{
    public function testSymfonyNativeRootServicesImportTargetExists(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/config/services.symfony-native.yaml');
    }

    public function testSymfonyNativeRootServicesImportTargetContainsLayeredImports(): void
    {
        $content = $this->read('config/services.symfony-native.yaml');

        foreach ([
            'services/infrastructure.yaml',
            'services/cache.yaml',
            'services/read_model.yaml',
            'services/application.yaml',
            'services/http.yaml',
            'services/ops.yaml',
            'services/core.yaml',
            'services/tagging.yaml',
        ] as $import) {
            self::assertStringContainsString($import, $content);
        }
    }

    public function testSymfonyNativeRootRoutesImportTargetExists(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/config/routes.symfony-native.yaml');
    }

    public function testSymfonyNativeRootRoutesImportTargetUsesCorrectedRouteMap(): void
    {
        $content = $this->read('config/routes.symfony-native.yaml');

        self::assertStringContainsString('routes/tagging_native.yaml', $content);
        self::assertStringNotContainsString('routes/tagging.yaml', $content);
    }

    private function read(string $relativePath): string
    {
        $content = file_get_contents(dirname(__DIR__) . '/' . $relativePath);
        self::assertIsString($content);

        return $content;
    }
}
