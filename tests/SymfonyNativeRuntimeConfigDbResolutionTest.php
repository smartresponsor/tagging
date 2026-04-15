<?php

declare(strict_types=1);

namespace Tests;

use App\Infrastructure\Config\TagRuntimeConfigFactory;
use PHPUnit\Framework\TestCase;

final class SymfonyNativeRuntimeConfigDbResolutionTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('DB_DSN');
        putenv('DB_USER');
        putenv('DB_PASS');
        putenv('DB_HOST');
        putenv('DB_PORT');
        putenv('POSTGRES_DB');
        putenv('POSTGRES_USER');
        putenv('POSTGRES_PASSWORD');
        putenv('TAG_ENTITY_TYPES');

        parent::tearDown();
    }

    public function testExplicitDbEnvironmentValuesTakePrecedence(): void
    {
        putenv('DB_DSN=pgsql:host=db.internal;port=5544;dbname=tagging');
        putenv('DB_USER=runtime-user');
        putenv('DB_PASS=runtime-pass');
        putenv('POSTGRES_DB=ignored-db');
        putenv('POSTGRES_USER=ignored-user');
        putenv('POSTGRES_PASSWORD=ignored-pass');

        $config = TagRuntimeConfigFactory::fromGlobals();

        self::assertSame('pgsql:host=db.internal;port=5544;dbname=tagging', $config->dbDsn);
        self::assertSame('runtime-user', $config->dbUser);
        self::assertSame('runtime-pass', $config->dbPass);
    }

    public function testDbFallbackBuildsDsnFromPostgresEnvironment(): void
    {
        putenv('DB_HOST=postgres.internal');
        putenv('DB_PORT=6543');
        putenv('POSTGRES_DB=tagging_app');
        putenv('POSTGRES_USER=tagging_user');
        putenv('POSTGRES_PASSWORD=tagging_pass');
        putenv('TAG_ENTITY_TYPES= , product, ,category , ');

        $config = TagRuntimeConfigFactory::fromGlobals();

        self::assertSame('pgsql:host=postgres.internal;port=6543;dbname=tagging_app', $config->dbDsn);
        self::assertSame('tagging_user', $config->dbUser);
        self::assertSame('tagging_pass', $config->dbPass);
        self::assertSame(['product', 'category'], $config->entityTypes);
    }
}
