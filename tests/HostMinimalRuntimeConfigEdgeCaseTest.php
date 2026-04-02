<?php

declare(strict_types=1);

namespace Tests;

use App\HostMinimal\Container\HostMinimalRuntimeConfig;
use PHPUnit\Framework\TestCase;

final class HostMinimalRuntimeConfigEdgeCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('TAG_ENTITY_TYPES');
        putenv('TENANT');
        putenv('DB_DSN');
        putenv('DB_USER');
        putenv('DB_PASS');
        putenv('DB_HOST');
        putenv('DB_PORT');
        putenv('POSTGRES_DB');
        putenv('POSTGRES_USER');
        putenv('POSTGRES_PASSWORD');

        parent::tearDown();
    }

    public function testBlankEntityTypeTokensCollapseBackToWildcard(): void
    {
        putenv('TAG_ENTITY_TYPES= ,  ,   ');

        $config = HostMinimalRuntimeConfig::fromGlobals();

        self::assertSame(['*'], $config->entityTypes);
    }

    public function testBlankTenantFallsBackToDemo(): void
    {
        putenv('TENANT=');

        $config = HostMinimalRuntimeConfig::fromGlobals();

        self::assertSame('demo', $config->defaultTenant);
    }

    public function testDbFallbackUsesBuiltInDefaultsWhenEnvironmentIsMissing(): void
    {
        $config = HostMinimalRuntimeConfig::fromGlobals();

        self::assertSame('pgsql:host=localhost;port=5432;dbname=app', $config->dbDsn);
        self::assertSame('app', $config->dbUser);
        self::assertSame('app', $config->dbPass);
    }
}
