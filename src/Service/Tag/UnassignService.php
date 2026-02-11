<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\Infra\Outbox\OutboxPublisher;
use DateTimeImmutable;
use PDO;
use Throwable;

/**
 *
 */

/**
 *
 */
final class UnassignService
{
    /**
     * @param \PDO $pdo
     * @param \App\Infra\Outbox\OutboxPublisher $outbox
     * @param \App\Service\Tag\IdempotencyStore|null $idem
     */
    public function __construct(
        private readonly PDO               $pdo,
        private readonly OutboxPublisher   $outbox,
        private readonly ?IdempotencyStore $idem = null,
    )
    {
    }

    /** @return array{ok:bool, not_found?:bool, duplicated?:bool} */
    public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        $checksum = hash('sha256', implode('|', [$tenant, $tagId, $entityType, $entityId]));
        if ($idemKey && $this->idem) {
            $st = $this->idem->begin($tenant, $idemKey, 'tag.unassign', $checksum);
            if ($st['state'] === 'duplicate') {
                return ['ok' => true, 'duplicated' => true];
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
                    'at' => (new DateTimeImmutable())->format(DATE_ATOM),
                ]);
            }

            $this->pdo->commit();
            if ($idemKey && $this->idem) $this->idem->complete($tenant, $idemKey, ['ok' => true]);
            return ['ok' => true, 'not_found' => !$deleted];
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['ok' => false];
        }
    }
}
