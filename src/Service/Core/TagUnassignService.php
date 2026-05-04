<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Core\Tag\TagLink;
use App\Tagging\Infrastructure\Outbox\Tag\TagOutboxPublisher;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagUnassignService implements TagUnassignOperationInterface
{
    private const string ACTION = 'tag.unassign';

    private TagErrorSink $errorSink;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TagEntityRepositoryInterface $tagEntities,
        private TagOutboxPublisher $outbox,
        private ?TagIdempotencyStore $idem = null,
        TagErrorSink|callable|null $errorSink = null,
    ) {
        $this->errorSink = TagErrorSinkFactory::from($errorSink);
    }

    /** @return array{ok:bool, not_found?:bool, duplicated?:bool, conflict?:bool, code?:string} */
    public function unassign(
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

        $this->entityManager->beginTransaction();
        try {
            if (!$this->tagExists($tenant, $tagId)) {
                $this->entityManager->rollback();
                $result = ['ok' => false, 'code' => 'tag_not_found'];
                $this->completeIdempotency($tenant, $idemKey, $result);

                return $result;
            }

            $link = $this->entityManager->getRepository(TagLink::class)->findOneBy([
                'tenant' => $tenant,
                'entityType' => $entityType,
                'entityId' => $entityId,
                'tagId' => $tagId,
            ]);
            $deleted = $link instanceof TagLink;
            if ($deleted) {
                $this->entityManager->remove($link);
                $this->outbox->publish($tenant, 'tag.unassigned', [
                    'tenant' => $tenant,
                    'tag_id' => $tagId,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'at' => new \DateTimeImmutable()->format(DATE_ATOM),
                ]);
            }

            $result = ['ok' => true, 'not_found' => !$deleted];

            $this->entityManager->flush();
            $this->entityManager->commit();
            $this->completeIdempotency($tenant, $idemKey, $result);

            return $result;
        } catch (\Throwable $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            $this->report($e, [
                'tenant' => $tenant,
                'tag_id' => $tagId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);

            return ['ok' => false, 'code' => 'unassign_failed'];
        }
    }

    /** @return array{ok:bool, not_found?:bool, duplicated?:bool, conflict?:bool, code?:string}|null */
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

    /** @param array{ok:bool, not_found?:bool, duplicated?:bool, conflict?:bool, code?:string} $result */
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
            'code' => 'tag.unassign_failed',
            'message' => $e->getMessage(),
            'exception' => $e::class,
            'context' => $context,
        ]);
    }
}
