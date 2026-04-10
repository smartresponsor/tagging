<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Infrastructure\Outbox\Tag\OutboxPublisher;

final readonly class AssignService implements AssignOperationInterface
{
    private const ACTION = 'tag.assign';

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
    public function assign(
        string $tenant,
        string $tagId,
        string $entityType,
        string $entityId,
        ?string $idemKey = null,
    ): array {
        $idempotencyDecision = $this->beginIdempotency($tenant, $tagId, $entityType, $entityId, $idemKey);
        if (null !== $idempotencyDecision) {
            return $idempotencyDecision;
        }

        $this->pdo->beginTransaction();
        try {
            if (!$this->tagExists($tenant, $tagId)) {
                $this->pdo->rollBack();

                return ['ok' => false, 'code' => 'tag_not_found'];
            }

            $created = $this->insertAssignment($tenant, $tagId, $entityType, $entityId);

            if ($created) {
                $this->publishAssignedEvent($tenant, $tagId, $entityType, $entityId);
            }

            $result = $this->assignmentResult($created);

            $this->pdo->commit();
            $this->completeIdempotency($tenant, $idemKey, $result);

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->report($e, [
                'tenant' => $tenant,
                'tag_id' => $tagId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);

            return ['ok' => false, 'code' => 'assign_failed'];
        }
    }

    /** @return array{ok:bool, duplicated?:bool, conflict?:bool, code?:string}|null */
    private function beginIdempotency(
        string $tenant,
        string $tagId,
        string $entityType,
        string $entityId,
        ?string $idemKey,
    ): ?array {
        if (null === $idemKey || '' === $idemKey || null === $this->idem) {
            return null;
        }

        return TagIdempotencyHelper::begin(
            $this->idem,
            new TagIdempotencyRequest(
                $tenant,
                self::ACTION,
                $tagId,
                $entityType,
                $entityId,
                $idemKey,
            ),
        );
    }

    private function tagExists(string $tenant, string $tagId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM tag_entity WHERE tenant=:tenant AND id=:id');
        $statement->execute([':tenant' => $tenant, ':id' => $tagId]);

        return false !== $statement->fetchColumn();
    }

    private function insertAssignment(string $tenant, string $tagId, string $entityType, string $entityId): bool
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id)
             VALUES (:tenant,:entity_type,:entity_id,:tag_id)
             ON CONFLICT (tenant, entity_type, entity_id, tag_id) DO NOTHING
             RETURNING 1',
        );
        $statement->execute([
            ':tenant' => $tenant,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':tag_id' => $tagId,
        ]);

        return false !== $statement->fetchColumn();
    }

    private function publishAssignedEvent(string $tenant, string $tagId, string $entityType, string $entityId): void
    {
        $this->outbox->publish($tenant, 'tag.assigned', [
            'tenant' => $tenant,
            'tag_id' => $tagId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }

    /** @return array{ok:bool, duplicated?:bool} */
    private function assignmentResult(bool $created): array
    {
        return $created ? ['ok' => true] : ['ok' => true, 'duplicated' => true];
    }

    /** @param array{ok:bool, duplicated?:bool, conflict?:bool, code?:string} $result */
    private function completeIdempotency(string $tenant, ?string $idemKey, array $result): void
    {
        if (null === $idemKey || '' === $idemKey || null === $this->idem) {
            return;
        }

        $this->idem->complete($tenant, $idemKey, $result);
    }

    private function report(\Throwable $e, array $context = []): void
    {
        $this->errorSink->report([
            'code' => 'tag.assign_failed',
            'message' => $e->getMessage(),
            'exception' => $e::class,
            'context' => $context,
        ]);
    }
}
