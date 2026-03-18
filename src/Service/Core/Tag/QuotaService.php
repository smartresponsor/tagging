<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

final readonly class QuotaService
{
    public function __construct(private ?\PDO $pdo = null, private array $cfg = [])
    {
    }

    public function canCreateTag(string $tenantId): array
    {
        $max = (int) ($this->cfg['quotas']['max_tags'] ?? 0);
        if ($max <= 0) {
            return ['ok' => true];
        }

        $count = $this->countBySql('SELECT COUNT(*) FROM tag_entity WHERE tenant = :tenant', $tenantId);

        return ['ok' => $count < $max, 'used' => $count, 'max' => $max];
    }

    public function canAssign(string $tenantId): array
    {
        $max = (int) ($this->cfg['quotas']['max_assignments'] ?? 0);
        if ($max <= 0) {
            return ['ok' => true];
        }

        $count = $this->countBySql('SELECT COUNT(*) FROM tag_link WHERE tenant = :tenant', $tenantId);

        return ['ok' => $count < $max, 'used' => $count, 'max' => $max];
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
        } catch (\Throwable) {
            return 0;
        }
    }
}
