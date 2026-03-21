<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class HostMinimalSurfaceTest extends TestCase
{
    public function testStatusRouteIsReachableWithoutComposerAutoload(): void
    {
        $container = require dirname(__DIR__).'/host-minimal/bootstrap.php';
        $routerFactory = require dirname(__DIR__).'/host-minimal/route.php';
        $dispatch = $routerFactory($container);

        [$code, $headers, $body] = $dispatch('GET', '/tag/_status', ['headers' => [], 'query' => [], 'body' => null]);
        $payload = json_decode($body, true);

        self::assertSame(200, $code);
        self::assertIsArray($payload);
        self::assertSame('tag', $payload['service'] ?? null);
        self::assertSame('host-minimal', $payload['runtime'] ?? null);
        self::assertSame('/tag/_surface', $payload['surface']['discovery'] ?? null);
        self::assertSame('no-store', $headers['Cache-Control'] ?? null);
        self::assertArrayHasKey('X-Tag-Version', $headers);
        self::assertArrayHasKey('db', $payload);
        self::assertArrayHasKey('available', $payload['db']);
        self::assertArrayHasKey('ok', $payload['db']);
    }

    public function testContractOnlyDocumentsRunnableMinimalHostSurface(): void
    {
        $contract = file_get_contents(dirname(__DIR__).'/contracts/http/tag-openapi.yaml');
        self::assertIsString($contract);
        self::assertStringContainsString('/tag/_status', $contract);
        self::assertStringContainsString('/tag/_surface', $contract);
        self::assertStringNotContainsString('/tag/{id}/synonym', $contract);
        self::assertStringNotContainsString('/tag/redirect/{fromId}', $contract);
    }
}
