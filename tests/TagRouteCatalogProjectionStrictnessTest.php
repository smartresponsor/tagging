<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRouteCatalogProjectionStrictnessTest extends TestCase
{
    public function testCanonicalCatalogPreservesPublicFlagsAndResponseHeaders(): void
    {
        $catalog = require __DIR__ . '/../config/tag_route_catalog.php';
        self::assertIsArray($catalog);

        $routes = [];
        foreach (($catalog['routes'] ?? []) as $route) {
            if (!is_array($route)) {
                continue;
            }

            $operation = (string) ($route['operation'] ?? '');
            if ('' === $operation) {
                continue;
            }

            $routes[$operation] = $route;
        }

        self::assertTrue($routes['status']['public'] ?? false);
        self::assertTrue($routes['discovery']['public'] ?? false);
        self::assertFalse($routes['webhooks_list']['public'] ?? true);
        self::assertFalse($routes['webhooks_subscribe']['public'] ?? true);
        self::assertFalse($routes['webhooks_test']['public'] ?? true);
        self::assertSame('X-Tag-Version', $routes['status']['response_header'] ?? null);
        self::assertSame('X-Tag-Surface-Version', $routes['discovery']['response_header'] ?? null);
        self::assertSame('#^/tag/([A-Za-z0-9]{26})$#', $routes['read']['pattern'] ?? null);
        self::assertSame('#^/tag/([A-Za-z0-9]{26})/assign$#', $routes['assign']['pattern'] ?? null);
    }

    public function testPublicSurfaceProjectionContainsOnlyPublicOperations(): void
    {
        $surface = require __DIR__ . '/../config/tag_public_surface.php';
        self::assertIsArray($surface);

        $routeMap = $surface['route'] ?? null;
        self::assertIsArray($routeMap);

        self::assertSame('/tag/_status', $routeMap['status'] ?? null);
        self::assertSame('/tag/_surface', $routeMap['discovery'] ?? null);
        self::assertSame('POST /tag/assignments/bulk', $routeMap['assignments_bulk'] ?? null);
        self::assertSame('POST /tag/assignments/bulk-to-entity', $routeMap['assignments_bulk_to_entity'] ?? null);
        self::assertSame('GET /tag/search', $routeMap['search'] ?? null);
        self::assertSame('GET /tag/suggest', $routeMap['suggest'] ?? null);
        self::assertArrayNotHasKey('webhooks_list', $routeMap);
        self::assertArrayNotHasKey('webhooks_subscribe', $routeMap);
        self::assertArrayNotHasKey('webhooks_test', $routeMap);
    }
}
