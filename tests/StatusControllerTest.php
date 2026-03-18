<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\RuntimeVersion;
use App\Http\Api\Tag\StatusController;
use PHPUnit\Framework\TestCase;

final class StatusControllerTest extends TestCase
{
    public function testStatusShape(): void
    {
        $payload = (new StatusController())->status();
        self::assertTrue($payload['ok']);
        self::assertSame('tag', $payload['service']);
        self::assertArrayHasKey('ts', $payload);
        self::assertArrayHasKey('db', $payload);
        self::assertSame(['available' => false, 'ok' => false], $payload['db']);
        self::assertSame(RuntimeVersion::read(), $payload['version']);
    }

    public function testStatusCanProbeDatabase(): void
    {
        $payload = (new StatusController(static fn (): bool => true, 'test-version'))->status();
        self::assertSame('test-version', $payload['version']);
        self::assertSame(['available' => true, 'ok' => true], $payload['db']);
    }

    public function testStatusMarksDatabaseUnavailableOnProbeFailure(): void
    {
        $payload = (new StatusController(static function (): bool {
            throw new \RuntimeException('db_down');
        }))->status();

        self::assertTrue($payload['db']['available']);
        self::assertFalse($payload['db']['ok']);
        self::assertSame('db_unavailable', $payload['db']['error']);
    }
}
