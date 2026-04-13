<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeBaselineSurfaceTest extends TestCase
{
    public function testSymfonyNativeBaselineFilesExist(): void
    {
        $root = dirname(__DIR__);

        self::assertFileExists($root . '/src/Kernel.php');
        self::assertFileExists($root . '/config/bootstrap.php');
        self::assertFileExists($root . '/config/bundles.php');
        self::assertFileExists($root . '/config/services.yaml');
        self::assertFileExists($root . '/config/routes.yaml');
        self::assertFileExists($root . '/config/packages/framework.yaml');
        self::assertFileExists($root . '/config/packages/routing.yaml');
        self::assertFileExists($root . '/config/packages/cache.yaml');
        self::assertFileExists($root . '/bin/console');
    }

    public function testSymfonyNativeLayeredServiceMapsExist(): void
    {
        $root = dirname(__DIR__);

        self::assertFileExists($root . '/config/services/infrastructure.yaml');
        self::assertFileExists($root . '/config/services/cache.yaml');
        self::assertFileExists($root . '/config/services/read_model.yaml');
        self::assertFileExists($root . '/config/services/application.yaml');
        self::assertFileExists($root . '/config/services/http.yaml');
        self::assertFileExists($root . '/config/services/ops.yaml');
        self::assertFileExists($root . '/config/services/core.yaml');
        self::assertFileExists($root . '/config/services/tagging.yaml');
    }

    public function testSymfonyNativeRouteBaselineExists(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/config/routes/tagging.yaml');
    }

    public function testHostMinimalRemainsOutsideNewBaselinePath(): void
    {
        self::assertFileExists(dirname(__DIR__) . '/host-minimal/bootstrap.php');
        self::assertFileExists(dirname(__DIR__) . '/public/index.symfony-native.php');
    }
}
