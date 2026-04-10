<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class HostMinimalBootstrapWiringTest extends TestCase
{
    public function testBootstrapResolvesSuggestControllerFactory(): void
    {
        $container = require dirname(__DIR__) . '/host-minimal/bootstrap.php';

        self::assertIsArray($container);
        self::assertArrayHasKey('suggestController', $container);
        self::assertIsCallable($container['suggestController']);
    }
}
