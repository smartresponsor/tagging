<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Infrastructure\Outbox\Tag\OutboxPublisher;

final readonly class AssignService implements AssignOperationInterface
{
    private TagErrorSink $errorSink;

    public function __construct(
        private \PDO $pdo,
        private OutboxPublisher $outbox,
        private ?IdempotencyStore $idem = null,
        TagErrorSink|callable|null $errorSink = null,
    ) {
        $this->errorSink = TagErrorSinkFactory::from($errorSink);
    }

    /** @return array{ok:bool, duplicated?:bool, conflict?:bool, code?:string} */
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
            $chk = $this->pdo->prepare('SELECT 1 FROM tag_entity WHERE tenant=:t AND id=:id');
            $chk->execute([':t' => $tenant, ':id' => $tagId]);
            if (!$chk->fetch()) {
                $this->pdo->rollBack();

                return ['ok' => false, 'code' => 'tag_not_found'];
            }

            $ins = $this->pdo->prepare(
                'INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id)
                 VALUES (:t,:et,:eid,:tid)
                 ON CONFLICT (tenant, entity_type, entity_id, tag_id) DO NOTHING
                 RETURNING 1',
            );
            $ins->execute([':t' => $tenant, ':et' => $entityType, ':eid' => $entityId, ':tid' => $tagId]);
            $created = false !== $ins->fetchColumn();

            if ($created) {
                $this->outbox->publish($tenant, 'tag.assigned', [
                    'tenant' => $tenant,
                    'tag_id' => $tagId,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                ]);
            }

            $result = $created ? ['ok' => true] : ['ok' => true, 'duplicated' => true];

            $this->pdo->commit();
            if ($idemKey && $this->idem) {
                $this->idem->complete($tenant, $idemKey, $result);
            }

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->report('tag.assign_failed', $e, [
                'tenant' => $tenant,
                'tag_id' => $tagId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);

            return ['ok' => false, 'code' => 'assign_failed'];
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
