<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Tagging\Data\Model\Tag\TagEntity;
use App\Tagging\Entity\Core\Tag\TagIdempotencyStore as IdempotencyStoreEntity;
use App\Tagging\Entity\Core\Tag\TagOutboxEvent;
use App\Tagging\Entity\Core\Tag\TagLink;
use App\Tagging\Http\Api\Tag\TagAssignController;
use App\Tagging\Infrastructure\Outbox\Tag\TagOutboxPublisher;
use App\Tagging\Infrastructure\Persistence\Tag\DoctrineTagEntityRepository;
use App\Tagging\Infrastructure\ReadModel\Tag\TagReadModel;
use App\Tagging\Service\Core\TagAssignService;
use App\Tagging\Service\Core\TagIdempotencyStore;
use App\Tagging\Service\Core\TagUnassignService;

abstract class TagIntegrationEvidenceTestCase extends IntegrationDbTestCase
{
    protected function insertTag(
        string $tenant,
        string $id,
        string $slug,
        string $name,
        int $weight = 0,
        string $locale = 'en',
    ): void {
        $this->entityManager()->persist(new TagEntity(
            $tenant,
            $id,
            $slug,
            $name,
            $locale,
            $weight,
        ));
        $this->entityManager()->flush();
    }

    protected function readModel(): TagReadModel
    {
        return new TagReadModel($this->entityManager());
    }

    protected function assignService(): TagAssignService
    {
        return new TagAssignService(
            $this->entityManager(),
            new DoctrineTagEntityRepository($this->entityManager()),
            new TagOutboxPublisher($this->entityManager()),
            new TagIdempotencyStore($this->entityManager()),
        );
    }

    protected function unassignService(): TagUnassignService
    {
        return new TagUnassignService(
            $this->entityManager(),
            new DoctrineTagEntityRepository($this->entityManager()),
            new TagOutboxPublisher($this->entityManager()),
            new TagIdempotencyStore($this->entityManager()),
        );
    }

    protected function assignController(): TagAssignController
    {
        return new TagAssignController(
            $this->assignService(),
            $this->unassignService(),
            ['entity_types' => ['*']],
        );
    }

    protected function countLinks(string $tenant): int
    {
        return (int) $this->entityManager()->createQueryBuilder()
            ->select('COUNT(l)')
            ->from(TagLink::class, 'l')
            ->where('l.tenant = :tenant')
            ->setParameter('tenant', $tenant)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function countOutbox(string $tenant, string $topic): int
    {
        return (int) $this->entityManager()->createQueryBuilder()
            ->select('COUNT(o)')
            ->from(TagOutboxEvent::class, 'o')
            ->where('o.tenant = :tenant')
            ->andWhere('o.topic = :topic')
            ->setParameter('tenant', $tenant)
            ->setParameter('topic', $topic)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function idempotencyStatus(string $tenant, string $key): ?string
    {
        /** @var IdempotencyStoreEntity|null $entity */
        $entity = $this->entityManager()->getRepository(IdempotencyStoreEntity::class)->findOneBy([
            'tenant' => $tenant,
            'key' => $key,
        ]);

        return $entity instanceof IdempotencyStoreEntity ? $entity->status() : null;
    }

    protected function decodeBody(string $body): array
    {
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
