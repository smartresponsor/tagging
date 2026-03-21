<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\HostMinimal\Container\HostMinimalContainer;
use PHPUnit\Framework\TestCase;

final class HostMinimalContainerTest extends TestCase
{
    public function testSharedEntryResolvesOnlyOnce(): void
    {
        $container = new HostMinimalContainer();
        $calls = 0;

        $container->share('sample', static function () use (&$calls): \stdClass {
            ++$calls;

            return new \stdClass();
        });

        $first = $container->get('sample');
        $second = $container->get('sample');

        self::assertSame($first, $second);
        self::assertSame(1, $calls);
    }

    public function testExportThrowsForUnknownEntry(): void
    {
        $container = new HostMinimalContainer();

        $this->expectException(\RuntimeException::class);
        $container->export(['missing']);
    }
}
