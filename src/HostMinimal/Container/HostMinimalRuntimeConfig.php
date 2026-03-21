<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\HostMinimal\Container;

final readonly class HostMinimalRuntimeConfig
{
    /**
     * @param array<string,mixed> $runtime
     * @param list<string>        $entityTypes
     * @param array<string,mixed> $webhook
     * @param array<string,mixed> $observability
     * @param array<string,mixed> $security
     */
    public function __construct(
        public array $runtime,
        public string $runtimeVersion,
        public string $dbDsn,
        public string $dbUser,
        public string $dbPass,
        public string $defaultTenant,
        public array $entityTypes,
        public array $webhook,
        public array $observability,
        public array $security,
    ) {
    }

    public static function fromGlobals(): self
    {
        $runtime = require dirname(__DIR__, 3).'/config/tag_runtime.php';
        $entityTypes = array_values(array_filter(
            array_map('trim', explode(',', self::env('TAG_ENTITY_TYPES', '*'))),
            static fn (string $value): bool => '' !== $value
        ));

        if ([] === $entityTypes) {
            $entityTypes = ['*'];
        }

        return new self(
            is_array($runtime) ? $runtime : [],
            is_array($runtime) ? (string) ($runtime['version'] ?? 'dev') : 'dev',
            self::env('DB_DSN', 'pgsql:host=localhost;port=5432;dbname=app'),
            self::env('DB_USER', 'app'),
            self::env('DB_PASS', 'app'),
            self::env('TENANT', 'demo'),
            $entityTypes,
            self::webhookConfig(),
            self::observabilityConfig(),
            self::securityConfig(),
        );
    }

    /** @return array<string,mixed> */
    private static function webhookConfig(): array
    {
        return [
            'timeout_ms' => 1000,
            'signature_header' => 'X-SR-Signature',
            'secret_fallback' => 'change-me',
            'events_allow' => ['tag.created', 'tag.updated', 'tag.deleted', 'tag.assigned', 'tag.merged', 'tag.split'],
            'registry_path' => self::env('TAG_WEBHOOK_REGISTRY_PATH', 'report/webhook/registry.json'),
            'audit_path' => self::env('TAG_AUDIT_PATH', 'report/tag/audit.ndjson'),
            'retries' => 5,
            'base_delay_ms' => 200,
            'max_delay_ms' => 10000,
            'spool_dir' => self::env('TAG_WEBHOOK_SPOOL_DIR', 'report/webhook/spool'),
            'dlq_path' => self::env('TAG_WEBHOOK_DLQ_PATH', 'report/webhook/dlq.ndjson'),
        ];
    }

    /** @return array<string,mixed> */
    private static function observabilityConfig(): array
    {
        return [
            'slowlog_path' => self::env('TAG_SLOWLOG_PATH', 'report/tag/slowlog.ndjson'),
            'threshold_ms' => [
                'read' => 500,
                'write' => 1000,
            ],
            'status' => [
                'checks' => [
                    ['name' => 'metrics_up', 'type' => 'counter_exists', 'key' => 'tag_up'],
                ],
            ],
        ];
    }

    /** @return array<string,mixed> */
    private static function securityConfig(): array
    {
        $secret = self::env('TAG_SIGNATURE_SECRET', '');

        return [
            'enforce' => '' !== $secret,
            'secret' => $secret,
            'skew_sec' => (int) self::env('TAG_SIGNATURE_SKEW_SEC', '120'),
            'nonce_ttl_sec' => (int) self::env('TAG_SIGNATURE_NONCE_TTL_SEC', '300'),
            'nonce_dir' => self::env('TAG_SIGNATURE_NONCE_DIR', 'var/cache/nonce'),
            'max_entries' => (int) self::env('TAG_SIGNATURE_NONCE_MAX', '100000'),
            'apply' => [
                'include' => ['/tag/**'],
                'exclude' => ['/tag/_status', '/tag/_surface', '/tag/_metrics'],
            ],
        ];
    }

    private static function env(string $name, string $default): string
    {
        $value = getenv($name);

        return is_string($value) && '' !== $value ? $value : $default;
    }
}
