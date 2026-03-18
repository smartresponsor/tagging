<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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
        self::assertFalse($payload['db']['available']);
        self::assertFalse($payload['db']['ok']);
        self::assertSame(RuntimeVersion::read(), $payload['version']);
    }

    public function testStatusCanExposeDbProbeResult(): void
    {
        $payload = (new StatusController(static fn(): bool => true, 'custom-version'))->status();
        self::assertTrue($payload['db']['available']);
        self::assertTrue($payload['db']['ok']);
        self::assertSame('custom-version', $payload['version']);
    }
}
