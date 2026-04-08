<?php

declare(strict_types=1);

namespace Tests;

use App\HostMinimal\Container\HostMinimalRuntimeConfig;
use PHPUnit\Framework\TestCase;

final class BootstrapMiddlewareExportTruthTest extends TestCase
{
    public function testBootstrapExportsCurrentMiddlewareEntries(): void
    {
        $container = require dirname(__DIR__).'/host-minimal/bootstrap.php';

        foreach (['idempotencyMiddleware', 'observeMiddleware', 'verifySignatureMiddleware', 'httpPipeline'] as $key) {
            self::assertArrayHasKey($key, $container);
            self::assertIsCallable($container[$key]);
        }
    }

    public function testRuntimeConfigSecurityShapeStillMatchesMiddlewareExpectations(): void
    {
        $config = HostMinimalRuntimeConfig::fromGlobals();

        self::assertIsArray($config->security);
        self::assertArrayHasKey('enforce', $config->security);
        self::assertArrayHasKey('secret', $config->security);
        self::assertArrayHasKey('apply', $config->security);
        self::assertSame(['/tag/**'], $config->security['apply']['include'] ?? null);
        self::assertContains('/tag/_status', $config->security['apply']['exclude'] ?? []);
        self::assertContains('/tag/_surface', $config->security['apply']['exclude'] ?? []);
    }
}
