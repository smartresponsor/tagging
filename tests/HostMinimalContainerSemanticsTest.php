<?php

declare(strict_types=1);

namespace Tests;

use App\HostMinimal\Container\HostMinimalContainer;
use PHPUnit\Framework\TestCase;

final class HostMinimalContainerSemanticsTest extends TestCase
{
    public function testSharedEntryIsResolvedOnlyOnce(): void
    {
        $container = new HostMinimalContainer();
        $calls = 0;

        $container->share('answer', static function () use (&$calls): array {
            ++$calls;

            return ['value' => 42];
        });

        $first = $container->get('answer');
        $second = $container->get('answer');

        self::assertSame(['value' => 42], $first);
        self::assertSame($first, $second);
        self::assertSame(1, $calls);
    }

    public function testValueEntriesAndSelectiveExportRemainCallable(): void
    {
        $container = new HostMinimalContainer();
        $container->value('runtime', ['service' => 'tag']);
        $container->share('statusController', static fn (): string => 'status-controller');

        $export = $container->export(['runtime', 'statusController']);

        self::assertArrayHasKey('runtime', $export);
        self::assertArrayHasKey('statusController', $export);
        self::assertIsCallable($export['runtime']);
        self::assertIsCallable($export['statusController']);
        self::assertSame(['service' => 'tag'], $export['runtime']());
        self::assertSame('status-controller', $export['statusController']());
    }

    public function testUnknownEntriesFailFastForGetAndExport(): void
    {
        $container = new HostMinimalContainer();

        try {
            $container->get('missing');
            self::fail('Expected RuntimeException for missing get().');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('Unknown container entry: missing', $e->getMessage());
        }

        try {
            $container->export(['missing']);
            self::fail('Expected RuntimeException for missing export().');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('Cannot export unknown container entry: missing', $e->getMessage());
        }
    }
}
