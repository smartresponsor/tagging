<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Infrastructure\Outbox\Tag\OutboxPublisher;

final readonly class UnassignService
{
    public function __construct(
        private \PDO $pdo,
        private OutboxPublisher $outbox,
        private ?IdempotencyStore $idem = null,
    ) {
    }

    /** @return array{ok:bool, not_found?:bool, duplicated?:bool} */
    public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        $checksum = hash('sha256', implode('|', [$tenant, $tagId, $entityType, $entityId]));
        if ($idemKey && $this->idem) {
            $st = $this->idem->begin($tenant, $idemKey, 'tag.unassign', $checksum);
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
            $del = $this->pdo->prepare(
                'DELETE FROM tag_link WHERE tenant=:t AND entity_type=:et AND entity_id=:eid AND tag_id=:tid'
            );
            $del->execute([':t' => $tenant, ':et' => $entityType, ':eid' => $entityId, ':tid' => $tagId]);
            $deleted = $del->rowCount() > 0;

            if ($deleted) {
                $this->outbox->publish($tenant, 'tag.unassigned', [
                    'tenant' => $tenant, 'tag_id' => $tagId, 'entity_type' => $entityType, 'entity_id' => $entityId,
                    'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                ]);
            }

            $this->pdo->commit();
            if ($idemKey && $this->idem) {
                $this->idem->complete($tenant, $idemKey, ['ok' => true]);
            }

            return ['ok' => true, 'not_found' => !$deleted];
        } catch (\Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return ['ok' => false];
        }
    }
}
