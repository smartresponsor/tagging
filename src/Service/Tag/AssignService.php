<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Service\Tag;

use App\Infra\Outbox\OutboxPublisher;
use App\Domain\Event\TagAssigned;
use PDO;

final class AssignService
{
    public function __construct(
        private PDO $pdo,
        private OutboxPublisher $outbox,
        private ?IdempotencyStore $idem = null,
    ) {}

    /** @return array{ok:bool, duplicated?:bool} */
    public function assign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        $checksum = hash('sha256', implode('|', [$tenant,$tagId,$entityType,$entityId]));
        if ($idemKey && $this->idem) {
            $st = $this->idem->begin($tenant, $idemKey, 'tag.assign', $checksum);
            if ($st['state'] === 'duplicate') {
                return ['ok'=>true, 'duplicated'=>true];
            }
        }

        $this->pdo->beginTransaction();
        try {
            // Ensure tag exists (optional strict check)
            $chk = $this->pdo->prepare('SELECT 1 FROM tag_entity WHERE tenant=:t AND id=:id');
            $chk->execute([':t'=>$tenant, ':id'=>$tagId]);
            if (!$chk->fetch()) {
                $this->pdo->rollBack();
                return ['ok'=>false];
            }

            $ins = $this->pdo->prepare(
                'INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id)
                 VALUES (:t,:et,:eid,:tid)
                 ON CONFLICT (tenant, entity_type, entity_id, tag_id) DO NOTHING'
            );
            $ins->execute([':t'=>$tenant, ':et'=>$entityType, ':eid'=>$entityId, ':tid'=>$tagId]);

            $this->outbox->publish($tenant, 'tag.assigned', [
                'tenant'=>$tenant, 'tag_id'=>$tagId, 'entity_type'=>$entityType, 'entity_id'=>$entityId,
                'at'=>(new \DateTimeImmutable())->format(DATE_ATOM),
            ]);

            $this->pdo->commit();
            if ($idemKey && $this->idem) $this->idem->complete($tenant, $idemKey, ['ok'=>true]);
            return ['ok'=>true];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['ok'=>false];
        }
    }
}
