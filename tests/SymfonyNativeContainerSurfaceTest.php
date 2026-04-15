<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeContainerSurfaceTest extends TestCase
{
    public function testSymfonyNativeAutowiringOwnsApplicationServiceDiscovery(): void
    {
        $services = file_get_contents(dirname(__DIR__) . '/config/services.yaml');
        self::assertIsString($services);

        self::assertStringContainsString('App\\\\:', $services);
        self::assertStringContainsString("resource: '../src/'", $services);
        self::assertStringContainsString("- '../src/Kernel.php'", $services);
    }

    public function testActiveServiceMapsDoNotRegisterHostMinimalNamespace(): void
    {
        foreach ([
            'config/services.yaml',
            'config/services/infrastructure.yaml',
            'config/services/cache.yaml',
            'config/services/read_model.yaml',
            'config/services/application.yaml',
            'config/services/http.yaml',
            'config/services/ops.yaml',
            'config/services/core.yaml',
            'config/services/tagging.yaml',
        ] as $path) {
            $content = file_get_contents(dirname(__DIR__) . '/' . $path);
            self::assertIsString($content);
            self::assertStringNotContainsString('App\\\\HostMinimal\\\\', $content, $path);
            self::assertStringNotContainsString('host-minimal', $content, $path);
        }
    }

    public function testSymfonyKernelAndFrameworkBundleAreDeclaredAsRuntimeSurface(): void
    {
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        self::assertIsString($composer);

        self::assertStringContainsString('symfony/framework-bundle', $composer);
        self::assertStringContainsString('symfony/dependency-injection', $composer);
        self::assertStringContainsString('symfony/http-kernel', $composer);
    }
}
