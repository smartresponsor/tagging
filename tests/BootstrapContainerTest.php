<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class BootstrapContainerTest extends TestCase
{
    public function testBootstrapExposesExpectedCallableEntries(): void
    {
        $container = require dirname(__DIR__).'/host-minimal/bootstrap.php';
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
            'defaultTenant',
        ];

        foreach ($required as $key) {
            self::assertArrayHasKey($key, $container);
            self::assertIsCallable($container[$key]);
        }
    }
}
