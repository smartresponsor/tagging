<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagSymfonyNativeContainerSurfaceTest extends TestCase
{
    public function testPackageServiceDiscoveryDoesNotReferenceStandaloneKernel(): void
    {
        $services = file_get_contents(dirname(__DIR__) . '/config/services.yaml');
        self::assertIsString($services);

        self::assertStringNotContainsString('../src/Kernel.php', $services);
        self::assertStringContainsString('services/tag_infrastructure.yaml', $services);
        self::assertStringContainsString('services/tag_http.yaml', $services);
    }

    public function testActiveServiceMapsDoNotRegisterHostMinimalNamespace(): void
    {
        foreach ([
            'config/services.yaml',
            'config/services/tag_infrastructure.yaml',
            'config/services/cache.yaml',
            'config/services/tag_read_model.yaml',
            'config/services/tag_application.yaml',
            'config/services/tag_http.yaml',
            'config/services/tag_ops.yaml',
            'config/services/tag_core.yaml',
            'config/services/tag_services.yaml',
        ] as $path) {
            $content = file_get_contents(dirname(__DIR__) . '/' . $path);
            self::assertIsString($content);
            self::assertStringNotContainsString('App\\HostMinimal\\', $content, $path);
            self::assertStringNotContainsString('host-minimal', $content, $path);
        }
    }

    public function testBundleAndContainerDependenciesRemainDeclared(): void
    {
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        self::assertIsString($composer);

        self::assertStringContainsString('symfony/dependency-injection', $composer);
        self::assertStringContainsString('symfony/dependency-injection', $composer);
        self::assertStringContainsString('symfony/http-kernel', $composer);
    }
}
