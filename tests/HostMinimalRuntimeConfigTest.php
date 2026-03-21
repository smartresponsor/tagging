<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\HostMinimal\Container\HostMinimalRuntimeConfig;
use PHPUnit\Framework\TestCase;

final class HostMinimalRuntimeConfigTest extends TestCase
{
    public function testRuntimeConfigProvidesEntityTypesFallback(): void
    {
        putenv('TAG_ENTITY_TYPES');

        $cfg = HostMinimalRuntimeConfig::fromGlobals();

        self::assertSame(['*'], $cfg->entityTypes);
        self::assertNotSame('', $cfg->runtimeVersion);
        self::assertIsArray($cfg->runtime);
        self::assertIsArray($cfg->security);
        self::assertArrayHasKey('apply', $cfg->security);
    }
}
