<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class BootstrapControllerResolutionTruthTest extends TestCase
{
    public function testSymfonyNativeHttpServiceMapRegistersControllerSurface(): void
    {
        $http = file_get_contents(dirname(__DIR__) . '/config/services/http.yaml');
        self::assertIsString($http);

        self::assertStringContainsString('App\Tagging\\Http\\Api\\Tag\\', $http);
        self::assertStringContainsString('../../src/Http/Api/Tag/', $http);
    }

    public function testSymfonyNativeRouteMapReferencesExpectedControllers(): void
    {
        $routes = file_get_contents(dirname(__DIR__) . '/config/routes/tagging_native.yaml');
        self::assertIsString($routes);

        foreach ([
            'App\Tagging\\Http\\Api\\Tag\\TagController::create',
            'App\Tagging\\Http\\Api\\Tag\\TagController::get',
            'App\Tagging\\Http\\Api\\Tag\\TagController::patch',
            'App\Tagging\\Http\\Api\\Tag\\TagController::delete',
            'App\Tagging\\Http\\Api\\Tag\\AssignController::assign',
            'App\Tagging\\Http\\Api\\Tag\\AssignController::unassign',
            'App\Tagging\\Http\\Api\\Tag\\AssignController::bulk',
            'App\Tagging\\Http\\Api\\Tag\\AssignController::assignBulkToEntity',
            'App\Tagging\\Http\\Api\\Tag\\AssignmentReadController::listByEntity',
            'App\Tagging\\Http\\Api\\Tag\\SearchController::get',
            'App\Tagging\\Http\\Api\\Tag\\SuggestController::get',
            'App\Tagging\\Http\\Api\\Tag\\StatusController::status',
            'App\Tagging\\Http\\Api\\Tag\\SurfaceController::surface',
            'App\Tagging\\Http\\Api\\Tag\\TagWebhookController::list',
            'App\Tagging\\Http\\Api\\Tag\\TagWebhookController::subscribe',
            'App\Tagging\\Http\\Api\\Tag\\TagWebhookController::test',
        ] as $controller) {
            self::assertStringContainsString($controller, $routes);
        }
    }
}
