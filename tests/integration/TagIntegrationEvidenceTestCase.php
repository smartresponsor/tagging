<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagIdempotencyStoreEntity;
use App\Tagging\Entity\Tag\TagOutboxEventEntity;
use App\Tagging\Repository\Outbox\TagOutboxPublisher;
use App\Tagging\Repository\Storage\TagDoctrineRepository;
use App\Tagging\Repository\TagReadModel;
use App\Tagging\Repository\TagDoctrineTransactionRunner;
use App\Tagging\Service\Core\TagAssignService;
use App\Tagging\Repository\TagIdempotencyStore;
use App\Tagging\Service\Core\TagUnassignService;

abstract class TagIntegrationEvidenceTestCase extends TagIntegrationDbTestCase
{
    protected function insertTag(
        string $tenant,
        string $id,
        string $slug,
        string $label,
        int $weight = 0,
        string $locale = 'en',
    ): void {
        $this->entityManager()->persist(TagEntity::create($tenant, $id, $slug, $label, $locale, $weight));
        $this->entityManager()->flush();
    }

    protected function readModel(): TagReadModel
    {
        return new TagReadModel($this->entityManager());
    }

    protected function assignService(): TagAssignService
    {
        $repository = new TagDoctrineRepository($this->entityManager());

        return new TagAssignService(
            $repository,
            $repository,
            new TagDoctrineTransactionRunner($this->entityManager()),
            new TagOutboxPublisher($this->entityManager()),
            new TagIdempotencyStore($this->entityManager()),
            static function (array $error): never {
                throw new \RuntimeException(($error['exception'] ?? 'Throwable') . ': ' . ($error['message'] ?? 'unknown error'));
            },
        );
    }

    protected function unassignService(): TagUnassignService
    {
        $repository = new TagDoctrineRepository($this->entityManager());

        return new TagUnassignService(
            $repository,
            $repository,
            new TagDoctrineTransactionRunner($this->entityManager()),
            new TagOutboxPublisher($this->entityManager()),
            new TagIdempotencyStore($this->entityManager()),
            static function (array $error): never {
                throw new \RuntimeException(($error['exception'] ?? 'Throwable') . ': ' . ($error['message'] ?? 'unknown error'));
            },
        );
    }

    protected function countLinks(string $tenant): int
    {
        return (int) $this->entityManager()->createQueryBuilder()
            ->select('COUNT(a)')
            ->from(TagAssignmentEntity::class, 'a')
            ->where('a.tenant = :tenant')
            ->setParameter('tenant', $tenant)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function countOutbox(string $tenant, string $topic): int
    {
        return (int) $this->entityManager()->createQueryBuilder()
            ->select('COUNT(o)')
            ->from(TagOutboxEventEntity::class, 'o')
            ->where('o.tenant = :tenant')
            ->andWhere('o.topic = :topic')
            ->setParameter('tenant', $tenant)
            ->setParameter('topic', $topic)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function idempotencyStatus(string $tenant, string $key): ?string
    {
        $entity = $this->entityManager()->getRepository(TagIdempotencyStoreEntity::class)->findOneBy([
            'tenant' => $tenant,
            'key' => $key,
        ]);

        return $entity instanceof TagIdempotencyStoreEntity ? $entity->status() : null;
    }

    /** @return array<string, mixed> */
    protected function decodeBody(string $body): array
    {
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
