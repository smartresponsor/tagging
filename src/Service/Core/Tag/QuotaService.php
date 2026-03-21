<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

final readonly class QuotaService
{
    private TagErrorSink $errorSink;

    public function __construct(
        private ?\PDO $pdo = null,
        private array $cfg = [],
        TagErrorSink|callable|null $errorSink = null,
    ) {
        $this->errorSink = TagErrorSinkFactory::from($errorSink);
    }

    public function canCreateTag(string $tenantId): array
    {
        return $this->quotaResult(
            $tenantId,
            (int) ($this->cfg['quotas']['max_tags'] ?? 0),
            'SELECT COUNT(*) FROM tag_entity WHERE tenant = :tenant',
            'quota_tags_exceeded'
        );
    }

    public function canAssign(string $tenantId): array
    {
        return $this->quotaResult(
            $tenantId,
            (int) ($this->cfg['quotas']['max_assignments'] ?? 0),
            'SELECT COUNT(*) FROM tag_link WHERE tenant = :tenant',
            'quota_assignments_exceeded'
        );
    }

    public function assertCanCreateTag(string $tenantId): void
    {
        $this->assertAllowed($this->canCreateTag($tenantId));
    }

    public function assertCanAssign(string $tenantId): void
    {
        $this->assertAllowed($this->canAssign($tenantId));
    }

    private function quotaResult(string $tenantId, int $max, string $sql, string $code): array
    {
        if ($max <= 0) {
            return ['ok' => true, 'used' => 0, 'max' => 0, 'remaining' => null, 'code' => null];
        }

        $count = $this->countBySql($sql, $tenantId);
        $ok = $count < $max;

        return [
            'ok' => $ok,
            'used' => $count,
            'max' => $max,
            'remaining' => max(0, $max - $count),
            'code' => $ok ? null : $code,
        ];
    }

    private function assertAllowed(array $result): void
    {
        if (($result['ok'] ?? false) === true) {
            return;
        }

        throw new \RuntimeException((string) ($result['code'] ?? 'quota_exceeded'));
    }

    private function countBySql(string $sql, string $tenantId): int
    {
        if (null === $this->pdo) {
            return 0;
        }

        try {
            $st = $this->pdo->prepare($sql);
            $st->execute([':tenant' => $tenantId]);

            return (int) $st->fetchColumn();
        } catch (\Throwable $e) {
            $this->report('quota.count_failed', $e, ['tenant' => $tenantId]);

            return 0;
        }
    }

    private function report(string $code, \Throwable $e, array $context = []): void
    {
        $this->errorSink->report([
            'code' => $code,
            'message' => $e->getMessage(),
            'exception' => $e::class,
            'context' => $context,
        ]);
    }
}
