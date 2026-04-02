<?php

declare(strict_types=1);

namespace Tests;

use App\HostMinimal\Container\HostMinimalRuntimeConfig;
use PHPUnit\Framework\TestCase;

final class HostMinimalRuntimeConfigOperationalShapeTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('TAG_WEBHOOK_REGISTRY_PATH');
        putenv('TAG_AUDIT_PATH');
        putenv('TAG_WEBHOOK_SPOOL_DIR');
        putenv('TAG_WEBHOOK_DLQ_PATH');
        putenv('TAG_SLOWLOG_PATH');
        putenv('TAG_SIGNATURE_SECRET');
        putenv('TAG_SIGNATURE_NONCE_DIR');

        parent::tearDown();
    }

    public function testWebhookAndObservabilityDefaultsExposeExpectedOperationalKeys(): void
    {
        $config = HostMinimalRuntimeConfig::fromGlobals();

        self::assertArrayHasKey('registry_path', $config->webhook);
        self::assertArrayHasKey('audit_path', $config->webhook);
        self::assertArrayHasKey('spool_dir', $config->webhook);
        self::assertArrayHasKey('dlq_path', $config->webhook);
        self::assertArrayHasKey('events_allow', $config->webhook);
        self::assertContains('tag.assigned', $config->webhook['events_allow'] ?? []);
        self::assertContains('tag.deleted', $config->webhook['events_allow'] ?? []);

        self::assertArrayHasKey('slowlog_path', $config->observability);
        self::assertArrayHasKey('threshold_ms', $config->observability);
        self::assertSame(500, $config->observability['threshold_ms']['read'] ?? null);
        self::assertSame(1000, $config->observability['threshold_ms']['write'] ?? null);
    }

    public function testOperationalPathsAndSecurityNonceDirCanBeOverriddenFromEnvironment(): void
    {
        putenv('TAG_WEBHOOK_REGISTRY_PATH=var/custom/webhook-registry.json');
        putenv('TAG_AUDIT_PATH=var/custom/audit.ndjson');
        putenv('TAG_WEBHOOK_SPOOL_DIR=var/custom/spool');
        putenv('TAG_WEBHOOK_DLQ_PATH=var/custom/dlq.ndjson');
        putenv('TAG_SLOWLOG_PATH=var/custom/slowlog.ndjson');
        putenv('TAG_SIGNATURE_SECRET=top-secret');
        putenv('TAG_SIGNATURE_NONCE_DIR=var/custom/nonce');

        $config = HostMinimalRuntimeConfig::fromGlobals();

        self::assertSame('var/custom/webhook-registry.json', $config->webhook['registry_path'] ?? null);
        self::assertSame('var/custom/audit.ndjson', $config->webhook['audit_path'] ?? null);
        self::assertSame('var/custom/spool', $config->webhook['spool_dir'] ?? null);
        self::assertSame('var/custom/dlq.ndjson', $config->webhook['dlq_path'] ?? null);
        self::assertSame('var/custom/slowlog.ndjson', $config->observability['slowlog_path'] ?? null);
        self::assertTrue($config->security['enforce'] ?? false);
        self::assertSame('var/custom/nonce', $config->security['nonce_dir'] ?? null);
    }
}
