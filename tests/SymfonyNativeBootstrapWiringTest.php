<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeBootstrapWiringTest extends TestCase
{
    public function testSymfonyNativeRootServicesImportLayeredMaps(): void
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
        ] as $expectedImport) {
            self::assertStringContainsString($expectedImport, $services);
        }
    }

    public function testSymfonyNativeRootRoutesImportCorrectedRouteMap(): void
    {
        $routes = file_get_contents(dirname(__DIR__) . '/config/routes.yaml');
        self::assertIsString($routes);

        self::assertStringContainsString('routes/tagging_native.yaml', $routes);
        self::assertStringNotContainsString('routes/tagging.yaml', $routes);
    }
}
