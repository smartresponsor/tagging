<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagBootstrapControllerResolutionTruthTest extends TestCase
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
            'App\Tagging\\Http\\Api\\Tag\\TagAssignController::assign',
            'App\Tagging\\Http\\Api\\Tag\\TagAssignController::unassign',
            'App\Tagging\\Http\\Api\\Tag\\TagAssignController::bulk',
            'App\Tagging\\Http\\Api\\Tag\\TagAssignController::assignBulkToEntity',
            'App\Tagging\\Http\\Api\\Tag\\TagAssignmentReadController::listByEntity',
            'App\Tagging\\Http\\Api\\Tag\\TagSearchController::get',
            'App\Tagging\\Http\\Api\\Tag\\TagSuggestController::get',
            'App\Tagging\\Http\\Api\\Tag\\TagStatusController::status',
            'App\Tagging\\Http\\Api\\Tag\\TagSurfaceController::surface',
            'App\Tagging\\Http\\Api\\Tag\\TagWebhookController::list',
            'App\Tagging\\Http\\Api\\Tag\\TagWebhookController::subscribe',
            'App\Tagging\\Http\\Api\\Tag\\TagWebhookController::test',
        ] as $controller) {
            self::assertStringContainsString($controller, $routes);
        }
    }
}
