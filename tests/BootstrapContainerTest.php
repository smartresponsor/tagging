<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class BootstrapContainerTest extends TestCase
{
    public function testSymfonyNativeContainerSurfaceUsesLayeredServiceMaps(): void
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

    public function testSymfonyNativeHttpLayerRegistersControllerAndMiddlewareNamespaces(): void
    {
        $http = file_get_contents(dirname(__DIR__) . '/config/services/http.yaml');
        self::assertIsString($http);

        self::assertStringContainsString('App\\Http\\Api\\Tag\\', $http);
        self::assertStringContainsString('App\\Http\\Api\\Tag\\Responder\\', $http);
        self::assertStringContainsString('App\\Http\\Api\\Tag\\Middleware\\', $http);
    }
}
