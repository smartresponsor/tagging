<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Factory\Error\TagErrorSinkFactory;
use App\Tagging\Repository\Outbox\TagOutboxPublisher;
use App\Tagging\Repository\TagIdempotencyStore;
use App\Tagging\RepositoryInterface\TagTransactionRunnerInterface;

final readonly class TagAssignService implements TagAssignOperationInterface
{
    private const string ACTION = 'tag.assign';

    private TagErrorSink $errorSink;

    public function __construct(
        private TagRepositoryInterface $repository,
        private TagCrudRepositoryInterface $tagEntities,
        private TagTransactionRunnerInterface $transaction,
        private TagOutboxPublisher $outbox,
        private ?TagIdempotencyStore $idem = null,
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

        try {
            return $this->transaction->run(function () use ($tenant, $tagId, $entityType, $entityId, $idemKey): array {
                if (!$this->tagExists($tenant, $tagId)) {
                    return ['ok' => false, 'code' => 'tag_not_found'];
                }

                $created = $this->insertAssignment($tenant, $tagId, $entityType, $entityId);
                if ($created) {
                    $this->publishAssignedEvent($tenant, $tagId, $entityType, $entityId);
                }

                $result = $this->assignmentResult($created);
                $this->completeIdempotency($tenant, $idemKey, $result);

                return $result;
            });
        } catch (\Throwable $e) {
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
        return null !== $this->tagEntities->findById($tenant, $tagId);
    }

    private function insertAssignment(string $tenant, string $tagId, string $entityType, string $entityId): bool
    {
        $existing = $this->repository->listAssignments($tenant, $tagId, $entityType, $entityId);
        if ([] !== $existing) {
            return false;
        }

        $this->repository->saveAssignment(
            $tenant,
            TagAssignmentEntity::create(
                $tenant,
                self::assignmentId($tenant, $tagId, $entityType, $entityId),
                $tagId,
                $entityType,
                $entityId,
            ),
        );

        return true;
    }

    private static function assignmentId(string $tenant, string $tagId, string $assignedType, string $assignedId): string
    {
        return 'ta_' . substr(hash('sha256', $tenant . "\0" . $assignedType . "\0" . $assignedId . "\0" . $tagId), 0, 32);
    }
    private function publishAssignedEvent(string $tenant, string $tagId, string $entityType, string $entityId): void
    {
        $this->outbox->publish($tenant, 'tag.assigned', [
            'tenant' => $tenant,
            'tag_id' => $tagId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'at' => new \DateTimeImmutable()->format(DATE_ATOM),
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
