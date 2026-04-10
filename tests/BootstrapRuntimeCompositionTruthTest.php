<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class BootstrapRuntimeCompositionTruthTest extends TestCase
{
    public function testBootstrapExposesCurrentCallableEntriesIncludingWebhookController(): void
    {
        $container = require dirname(__DIR__) . '/host-minimal/bootstrap.php';

        $required = [
            'runtime',
            'idempotencyMiddleware',
            'observeMiddleware',
            'verifySignatureMiddleware',
            'httpPipeline',
            'statusController',
            'surfaceController',
            'tagController',
            'assignController',
            'searchController',
            'suggestController',
            'assignmentReadController',
            'webhookController',
            'defaultTenant',
        ];

        foreach ($required as $key) {
            self::assertArrayHasKey($key, $container);
            self::assertIsCallable($container[$key]);
        }
    }

    public function testBootstrapRuntimeAndDefaultTenantEntriesResolveToExpectedShapes(): void
    {
        $container = require dirname(__DIR__) . '/host-minimal/bootstrap.php';

        $runtime = $container['runtime']();
        $defaultTenant = $container['defaultTenant']();

        self::assertIsArray($runtime);
        self::assertArrayHasKey('version', $runtime);
        self::assertArrayHasKey('service', $runtime);
        self::assertIsString($defaultTenant);
        self::assertNotSame('', trim($defaultTenant));
    }

    public function testBootstrapSourceStillUsesCompositionSegmentsAndRuntimeConfig(): void
    {
        $bootstrap = file_get_contents(__DIR__ . '/../host-minimal/bootstrap.php');
        self::assertIsString($bootstrap);

        self::assertStringContainsString('HostMinimalRuntimeConfig::fromGlobals()', $bootstrap);
        self::assertStringContainsString('$shareConfig = static function', $bootstrap);
        self::assertStringContainsString('$shareInfrastructure = static function', $bootstrap);
        self::assertStringContainsString('$shareMiddleware = static function', $bootstrap);
        self::assertStringContainsString('$shareControllers = static function', $bootstrap);
        self::assertStringContainsString('$shareWebhookServices = static function', $bootstrap);
        self::assertStringContainsString("'webhookController'", $bootstrap);
    }
}
