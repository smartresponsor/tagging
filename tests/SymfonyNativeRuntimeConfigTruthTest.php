<?php

declare(strict_types=1);

namespace Tests;

use App\Tagging\Infrastructure\Config\TagRuntimeConfigFactory;
use PHPUnit\Framework\TestCase;

final class SymfonyNativeRuntimeConfigTruthTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('TAG_SIGNATURE_SECRET');
        putenv('TAG_ENTITY_TYPES');
        putenv('TENANT');

        parent::tearDown();
    }

    public function testDefaultsKeepPublicHealthRoutesOutsideSignatureEnforcement(): void
    {
        $config = TagRuntimeConfigFactory::fromGlobals();

        self::assertFalse($config->security['enforce'] ?? true);
        self::assertSame(['/tag/**'], $config->security['apply']['include'] ?? null);
        self::assertSame(['/tag/_status', '/tag/_surface', '/tag/_metrics'], $config->security['apply']['exclude'] ?? null);
        self::assertSame('demo', $config->defaultTenant);
        self::assertSame(['*'], $config->entityTypes);
    }

    public function testSecretAndEntityTypesAreReadFromEnvironment(): void
    {
        putenv('TAG_SIGNATURE_SECRET=test-secret');
        putenv('TAG_ENTITY_TYPES=project,product,category');
        putenv('TENANT=tenant-alpha');

        $config = TagRuntimeConfigFactory::fromGlobals();

        self::assertTrue($config->security['enforce'] ?? false);
        self::assertSame('test-secret', $config->security['secret'] ?? null);
        self::assertSame(['project', 'product', 'category'], $config->entityTypes);
        self::assertSame('tenant-alpha', $config->defaultTenant);
    }
}
