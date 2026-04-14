<?php

declare(strict_types=1);

namespace Tests;

use App\Infrastructure\Config\TagRuntimeConfigFactory;
use PHPUnit\Framework\TestCase;

final class BootstrapMiddlewareExportTruthTest extends TestCase
{
    public function testSymfonyNativeHttpServiceMapKeepsMiddlewareNamespaceRegistered(): void
    {
        $http = file_get_contents(dirname(__DIR__) . '/config/services/http.yaml');
        self::assertIsString($http);

        self::assertStringContainsString('App\\Http\\Api\\Tag\\Middleware\\', $http);
        self::assertStringContainsString('../../src/Http/Api/Tag/Middleware/', $http);
    }

    public function testSymfonyNativeSecurityConfigFactoryMatchesMiddlewareExpectations(): void
    {
        $config = TagRuntimeConfigFactory::securityConfig();

        self::assertIsArray($config);
        self::assertArrayHasKey('enforce', $config);
        self::assertArrayHasKey('secret', $config);
        self::assertArrayHasKey('apply', $config);
        self::assertSame(['/tag/**'], $config['apply']['include'] ?? null);
        self::assertContains('/tag/_status', $config['apply']['exclude'] ?? []);
        self::assertContains('/tag/_surface', $config['apply']['exclude'] ?? []);
    }
}
