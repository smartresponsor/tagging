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

        self::assertStringContainsString('App\\Http\\Api\\Tag\\', $http);
        self::assertStringContainsString('../../src/Http/Api/Tag/', $http);
    }

    public function testSymfonyNativeRouteMapReferencesExpectedControllers(): void
    {
        $routes = file_get_contents(dirname(__DIR__) . '/config/routes/tagging_native.yaml');
        self::assertIsString($routes);

        foreach ([
            'App\\Http\\Api\\Tag\\TagController::create',
            'App\\Http\\Api\\Tag\\TagController::get',
            'App\\Http\\Api\\Tag\\TagController::patch',
            'App\\Http\\Api\\Tag\\TagController::delete',
            'App\\Http\\Api\\Tag\\AssignController::assign',
            'App\\Http\\Api\\Tag\\AssignController::unassign',
            'App\\Http\\Api\\Tag\\AssignController::bulk',
            'App\\Http\\Api\\Tag\\AssignController::assignBulkToEntity',
            'App\\Http\\Api\\Tag\\AssignmentReadController::listByEntity',
            'App\\Http\\Api\\Tag\\SearchController::get',
            'App\\Http\\Api\\Tag\\SuggestController::get',
            'App\\Http\\Api\\Tag\\StatusController::status',
            'App\\Http\\Api\\Tag\\SurfaceController::surface',
            'App\\Http\\Api\\Tag\\TagWebhookController::list',
            'App\\Http\\Api\\Tag\\TagWebhookController::subscribe',
            'App\\Http\\Api\\Tag\\TagWebhookController::test',
        ] as $controller) {
            self::assertStringContainsString($controller, $routes);
        }
    }
}
