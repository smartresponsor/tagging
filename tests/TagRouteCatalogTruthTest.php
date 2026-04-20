<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRouteCatalogTruthTest extends TestCase
{
    public function testPublicSurfaceIsProjectedFromCanonicalRouteCatalog(): void
    {
        $catalog = require __DIR__ . '/../config/tag_route_catalog.php';
        $surface = require __DIR__ . '/../config/tag_public_surface.php';

        self::assertIsArray($catalog);
        self::assertIsArray($surface);
        self::assertSame('p118-route-catalog-truth', $catalog['version'] ?? null);
        self::assertSame('p118-route-catalog-truth', $surface['version'] ?? null);
        self::assertSame('/tag/_status', $surface['route']['status'] ?? null);
        self::assertSame('/tag/_surface', $surface['route']['discovery'] ?? null);
        self::assertSame('POST /tag/assignments/bulk', $surface['route']['assignments_bulk'] ?? null);
        self::assertSame('GET /tag/search', $surface['route']['search'] ?? null);
        self::assertArrayNotHasKey('webhooks_list', $surface['route']);
    }

    public function testCanonicalRouteCatalogPointsAtCurrentPackageControllers(): void
    {
        $catalog = require __DIR__ . '/../config/tag_route_catalog.php';
        self::assertIsArray($catalog);

        $controllers = [];
        foreach (($catalog['routes'] ?? []) as $route) {
            if (!is_array($route)) {
                continue;
            }

            $controller = (string) ($route['controller'] ?? '');
            if ('' !== $controller) {
                $controllers[] = $controller;
            }
        }

        self::assertContains('App\\Tagging\\Http\\Api\\Tag\\StatusController::status', $controllers);
        self::assertContains('App\\Tagging\\Http\\Api\\Tag\\SurfaceController::surface', $controllers);
        self::assertContains('App\\Tagging\\Http\\Api\\Tag\\AssignController::bulk', $controllers);
        self::assertNotContains('App\\Http\\Api\\Tag\\StatusController::status', $controllers);
    }
}
