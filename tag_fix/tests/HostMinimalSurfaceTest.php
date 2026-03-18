<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class HostMinimalSurfaceTest extends TestCase
{
    public function testStatusRouteIsReachableWithoutComposerAutoload(): void
    {
        $container = require dirname(__DIR__) . '/host-minimal/bootstrap.php';
        $routerFactory = require dirname(__DIR__) . '/host-minimal/route.php';
        $dispatch = $routerFactory($container);

        [$code, , $body] = $dispatch('GET', '/tag/_status', ['headers' => [], 'query' => [], 'body' => null]);
        self::assertSame(200, $code);
        self::assertStringContainsString('service', $body);
    }

    public function testSurfaceRouteIsReachable(): void
    {
        $container = require dirname(__DIR__) . '/host-minimal/bootstrap.php';
        $routerFactory = require dirname(__DIR__) . '/host-minimal/route.php';
        $dispatch = $routerFactory($container);

        [$code, , $body] = $dispatch('GET', '/tag/_surface', ['headers' => [], 'query' => [], 'body' => null]);
        self::assertSame(200, $code);
        self::assertStringContainsString('/tag/_surface', $body);
    }
}
