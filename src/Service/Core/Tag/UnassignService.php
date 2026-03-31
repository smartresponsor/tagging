<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Infrastructure\Outbox\Tag\OutboxPublisher;

final readonly class UnassignService implements UnassignOperationInterface
{
    private const ACTION = 'tag.unassign';

    private TagErrorSink $errorSink;

    public function __construct(
        private \PDO $pdo,
        private OutboxPublisher $outbox,
        private ?IdempotencyStore $idem = null,
        TagErrorSink|callable|null $errorSink = null,
    ) {
        $this->errorSink = TagErrorSinkFactory::from($errorSink);
    }

    /** @return array{ok:bool, not_found?:bool, duplicated?:bool, conflict?:bool, code?:string} */
    public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        $idempotencyDecision = $this->beginIdempotency($tenant, $tagId, $entityType, $entityId, $idemKey);
        if (null !== $idempotencyDecision) {
            return $idempotencyDecision;
        }

        $this->pdo->beginTransaction();
        try {
            if (!$this->tagExists($tenant, $tagId)) {
                $this->pdo->rollBack();
                $result = ['ok' => false, 'code' => 'tag_not_found'];
                $this->completeIdempotency($tenant, $idemKey, $result);

                return $result;
            }

            $del = $this->pdo->prepare(
                'DELETE FROM tag_link WHERE tenant=:t AND entity_type=:et AND entity_id=:eid AND tag_id=:tid',
            );
            $del->execute([':t' => $tenant, ':et' => $entityType, ':eid' => $entityId, ':tid' => $tagId]);
            $deleted = $del->rowCount() > 0;

            if ($deleted) {
                $this->outbox->publish($tenant, 'tag.unassigned', [
                    'tenant' => $tenant,
                    'tag_id' => $tagId,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                ]);
            }

            $result = ['ok' => true, 'not_found' => !$deleted];

            $this->pdo->commit();
            $this->completeIdempotency($tenant, $idemKey, $result);

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->report('tag.unassign_failed', $e, [
                'tenant' => $tenant,
                'tag_id' => $tagId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);

            return ['ok' => false, 'code' => 'unassign_failed'];
        }
    }

    /** @return array{ok:bool, not_found?:bool, duplicated?:bool, conflict?:bool, code?:string}|null */
    private function beginIdempotency(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey): ?array
    {
        if (null === $idemKey || '' === $idemKey || null === $this->idem) {
            return null;
        }

        $checksum = hash('sha256', implode('|', [$tenant, $tagId, $entityType, $entityId]));
        $state = $this->idem->begin($tenant, $idemKey, self::ACTION, $checksum);

        return match ($state['state'] ?? null) {
            'duplicate' => $this->duplicateResult($state),
            'conflict' => ['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'],
            default => null,
        };
    }

    /** @param array<string,mixed> $state
     *  @return array{ok:bool, not_found?:bool, duplicated:bool, conflict?:bool, code?:string}
     */
    private function duplicateResult(array $state): array
    {
        $result = is_array($state['result'] ?? null) ? $state['result'] : ['ok' => true];
        $result['duplicated'] = true;

        return $result;
    }

    private function tagExists(string $tenant, string $tagId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM tag_entity WHERE tenant=:tenant AND id=:id');
        $statement->execute([':tenant' => $tenant, ':id' => $tagId]);

        return false !== $statement->fetchColumn();
    }

    /** @param array{ok:bool, not_found?:bool, duplicated?:bool, conflict?:bool, code?:string} $result */
    private function completeIdempotency(string $tenant, ?string $idemKey, array $result): void
    {
        if (null === $idemKey || '' === $idemKey || null === $this->idem) {
            return;
        }

        $this->idem->complete($tenant, $idemKey, $result);
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
