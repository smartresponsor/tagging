<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Infrastructure\Outbox\Tag\OutboxPublisher;

final readonly class AssignService
{
    public function __construct(
        private \PDO $pdo,
        private OutboxPublisher $outbox,
        private ?IdempotencyStore $idem = null,
    ) {
    }

    /** @return array{ok:bool, duplicated?:bool} */
    public function assign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        $checksum = hash('sha256', implode('|', [$tenant, $tagId, $entityType, $entityId]));
        if ($idemKey && $this->idem) {
            $st = $this->idem->begin($tenant, $idemKey, 'tag.assign', $checksum);
            if ('duplicate' === $st['state']) {
                $result = is_array($st['result'] ?? null) ? $st['result'] : ['ok' => true];
                $result['duplicated'] = true;

                return $result;
            }
            if ('conflict' === $st['state']) {
                return ['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'];
            }
        }

        $this->pdo->beginTransaction();
        try {
            // Ensure tag exists (optional strict check)
            $chk = $this->pdo->prepare('SELECT 1 FROM tag_entity WHERE tenant=:t AND id=:id');
            $chk->execute([':t' => $tenant, ':id' => $tagId]);
            if (!$chk->fetch()) {
                $this->pdo->rollBack();

                return ['ok' => false];
            }

            $ins = $this->pdo->prepare(
                'INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id)
                 VALUES (:t,:et,:eid,:tid)
                 ON CONFLICT (tenant, entity_type, entity_id, tag_id) DO NOTHING
                 RETURNING 1'
            );
            $ins->execute([':t' => $tenant, ':et' => $entityType, ':eid' => $entityId, ':tid' => $tagId]);
            $created = false !== $ins->fetchColumn();

            if ($created) {
                $this->outbox->publish($tenant, 'tag.assigned', [
                    'tenant' => $tenant, 'tag_id' => $tagId, 'entity_type' => $entityType, 'entity_id' => $entityId,
                    'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                ]);
            }

            $result = $created ? ['ok' => true] : ['ok' => true, 'duplicated' => true];

            $this->pdo->commit();
            if ($idemKey && $this->idem) {
                $this->idem->complete($tenant, $idemKey, $result);
            }

            return $result;
        } catch (\Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return ['ok' => false];
        }
    }
}
