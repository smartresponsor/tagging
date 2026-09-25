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

    public function testSymfonyNativeHttpLayerRegistersServiceAndFormNamespaces(): void
    {
        $http = file_get_contents(dirname(__DIR__) . '/config/services/tag_http.yaml');
        self::assertIsString($http);

        self::assertStringContainsString('App\Tagging\\Service\\Http\\Tag\\', $http);
        self::assertStringContainsString("resource: '../../src/Service/Http/Tag/'", $http);
        self::assertStringContainsString('App\Tagging\\Form\\Type\\', $http);
        self::assertStringNotContainsString('App\Tagging\\Http\\Api\\Tag\\', $http);
    }
}
