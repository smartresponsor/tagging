<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RouteContractHardeningTest extends TestCase
{
    public function testRuntimePublicSurfaceMatchesPackageHostedMetaRoutes(): void
    {
        $runtime = require dirname(__DIR__) . '/config/tag_runtime.php';
        $surface = $runtime['public_surface'] ?? [];

        self::assertSame(['method' => 'GET', 'path' => '/tag/_status', 'name' => 'status'], $surface[0] ?? null);
        self::assertSame(['method' => 'GET', 'path' => '/tag/_surface', 'name' => 'discovery'], $surface[1] ?? null);
    }

    public function testRouteConfigUsesCurrentControllerClasses(): void
    {
        $route = (string) file_get_contents(__DIR__ . '/../config/routes/tagging_native.yaml');

        self::assertStringContainsString('App\\Tagging\\Http\\Api\\Tag\\StatusController::status', $route);
        self::assertStringContainsString('App\\Tagging\\Http\\Api\\Tag\\SurfaceController::surface', $route);
        self::assertStringContainsString('App\\Tagging\\Http\\Api\\Tag\\AssignController::assign', $route);
        self::assertStringContainsString('App\\Tagging\\Http\\Api\\Tag\\AssignController::unassign', $route);
        self::assertStringContainsString('App\\Tagging\\Http\\Api\\Tag\\AssignmentReadController::listByEntity', $route);
        self::assertStringContainsString('App\\Tagging\\Http\\Api\\Tag\\SearchController::get', $route);
        self::assertStringContainsString('App\\Tagging\\Http\\Api\\Tag\\SuggestController::get', $route);
        self::assertStringNotContainsString('TagAssignmentController', $route);
        self::assertStringNotContainsString('TagSearchController', $route);
        self::assertStringNotContainsString('TagSuggestController', $route);
    }
}
