<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRouteCatalogProjectionRuntimeTruthTest extends TestCase
{
    public function testCatalogRoutesRemainAlignedWithRouteProjectionSource(): void
    {
        $catalog = require __DIR__ . '/../config/tag_route_catalog.php';
        $surface = require __DIR__ . '/../config/tag_public_surface.php';

        self::assertIsArray($catalog);
        self::assertIsArray($surface);

        $operations = [];
        foreach (($catalog['routes'] ?? []) as $route) {
            if (!is_array($route)) {
                continue;
            }

            $operation = (string) ($route['operation'] ?? '');
            if ('' === $operation) {
                continue;
            }

            $operations[] = $operation;
        }

        self::assertContains('status', $operations);
        self::assertContains('discovery', $operations);
        self::assertContains('assignments_bulk', $operations);
        self::assertContains('assignments_bulk_to_entity', $operations);
        self::assertContains('search', $operations);
        self::assertContains('suggest', $operations);

        $routeMap = $surface['route'] ?? null;
        self::assertIsArray($routeMap);
        self::assertArrayHasKey('status', $routeMap);
        self::assertArrayHasKey('discovery', $routeMap);
        self::assertArrayHasKey('assignments_bulk', $routeMap);
        self::assertArrayHasKey('assignments_bulk_to_entity', $routeMap);
        self::assertArrayHasKey('search', $routeMap);
        self::assertArrayHasKey('suggest', $routeMap);
    }

    public function testPublicProjectionStillFiltersPrivateWebhookRoutesOutOfSurface(): void
    {
        $catalog = require __DIR__ . '/../config/tag_route_catalog.php';
        $surface = require __DIR__ . '/../config/tag_public_surface.php';

        self::assertIsArray($catalog);
        self::assertIsArray($surface);

        $privateOps = [];
        foreach (($catalog['routes'] ?? []) as $route) {
            if (!is_array($route)) {
                continue;
            }

            if (false === ($route['public'] ?? false)) {
                $privateOps[] = (string) ($route['operation'] ?? '');
            }
        }

        $routeMap = $surface['route'] ?? null;
        self::assertIsArray($routeMap);

        self::assertContains('webhooks_list', $privateOps);
        self::assertContains('webhooks_subscribe', $privateOps);
        self::assertContains('webhooks_test', $privateOps);
        self::assertArrayNotHasKey('webhooks_list', $routeMap);
        self::assertArrayNotHasKey('webhooks_subscribe', $routeMap);
        self::assertArrayNotHasKey('webhooks_test', $routeMap);
    }
}
