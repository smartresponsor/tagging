<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRouteCatalogContractInvariantTest extends TestCase
{
    public function testCatalogPreservesExpectedReadAndWritePatterns(): void
    {
        $catalog = require __DIR__.'/../config/tag_route_catalog.php';
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

        self::assertSame('#^/tag/([A-Za-z0-9]{26})$#', $routes['read']['pattern'] ?? null);
        self::assertSame('#^/tag/([A-Za-z0-9]{26})$#', $routes['patch']['pattern'] ?? null);
        self::assertSame('#^/tag/([A-Za-z0-9]{26})$#', $routes['delete']['pattern'] ?? null);
        self::assertSame('#^/tag/([A-Za-z0-9]{26})/assign$#', $routes['assign']['pattern'] ?? null);
        self::assertSame('#^/tag/([A-Za-z0-9]{26})/unassign$#', $routes['unassign']['pattern'] ?? null);
    }

    public function testCatalogKeepsOperationalResponseHeadersVisible(): void
    {
        $catalog = require __DIR__.'/../config/tag_route_catalog.php';
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

        self::assertSame('X-Tag-Version', $routes['status']['response_header'] ?? null);
        self::assertSame('X-Tag-Surface-Version', $routes['discovery']['response_header'] ?? null);
        self::assertTrue($routes['status']['public'] ?? false);
        self::assertTrue($routes['discovery']['public'] ?? false);
    }
}
