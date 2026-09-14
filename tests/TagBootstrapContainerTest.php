<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagBootstrapContainerTest extends TestCase
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

    public function testSymfonyNativeHttpLayerRegistersServiceAndFormNamespaces(): void
    {
        $http = file_get_contents(dirname(__DIR__) . '/config/services/http.yaml');
        self::assertIsString($http);

        self::assertStringContainsString('App\Tagging\\Service\\Http\\Tag\\', $http);
        self::assertStringContainsString("resource: '../../src/Service/Http/Tag/'", $http);
        self::assertStringContainsString('App\Tagging\\Form\\Tag\\', $http);
        self::assertStringNotContainsString('App\Tagging\\Http\\Api\\Tag\\', $http);
    }
}
