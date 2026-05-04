<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Infrastructure\Persistence\Tag;

use App\Tagging\Data\Model\Tag\TagEntity;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class TagDoctrineEntityRepository implements TagEntityRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function existsSlug(string $tenant, string $slug, ?string $excludeId = null): bool
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(TagEntity::class, 'e')
            ->where('e.tenant = :tenant')
            ->andWhere('e.slug = :slug')
            ->setParameter('tenant', $tenant)
            ->setParameter('slug', $slug);

        if (null !== $excludeId && '' !== $excludeId) {
            $qb->andWhere('e.id <> :excludeId')->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findById(string $tenant, string $id): ?array
    {
        $entity = $this->entityManager->getRepository(TagEntity::class)->findOneBy([
            'tenant' => $tenant,
            'id' => $id,
        ]);

        return $entity instanceof TagEntity ? $this->toArray($entity) : null;
    }

    public function create(string $tenant, TagEntityCreateRecord $record): array
    {
        $entity = new TagEntity(
            $tenant,
            $record->id,
            $record->slug,
            $record->name,
            $record->locale,
            $record->weight,
        );

        $this->entityManager->persist($entity);
        $this->flushSafely();

        return $this->toArray($entity);
    }

    public function patch(string $tenant, string $id, array $patch): void
    {
        $entity = $this->entityManager->getRepository(TagEntity::class)->findOneBy([
            'tenant' => $tenant,
            'id' => $id,
        ]);
        if (!$entity instanceof TagEntity) {
            return;
        }

        $entity->patch(
            array_key_exists('name', $patch) ? (string) $patch['name'] : null,
            array_key_exists('locale', $patch) ? (string) $patch['locale'] : null,
            array_key_exists('weight', $patch) ? (int) $patch['weight'] : null,
        );

        $this->flushSafely();
    }

    public function delete(string $tenant, string $id): void
    {
        $entity = $this->entityManager->getRepository(TagEntity::class)->findOneBy([
            'tenant' => $tenant,
            'id' => $id,
        ]);
        if (!$entity instanceof TagEntity) {
            return;
        }

        $this->entityManager->remove($entity);
        $this->flushSafely();
    }

    /** @return array{id:string,slug:string,name:string,locale:string,weight:int,created_at?:string,updated_at?:string} */
    private function toArray(TagEntity $entity): array
    {
        return [
            'id' => $entity->id(),
            'slug' => $entity->slug(),
            'name' => $entity->name(),
            'locale' => (string) ($entity->locale() ?? ''),
            'weight' => $entity->weight(),
            'created_at' => $entity->createdAt()?->format(DATE_ATOM) ?? '',
            'updated_at' => $entity->updatedAt()?->format(DATE_ATOM) ?? '',
        ];
    }

    private function flushSafely(): void
    {
        try {
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            if ($this->entityManager->isOpen()) {
                $this->entityManager->clear();
            }
            if (str_contains($e::class, 'UniqueConstraintViolation')) {
                throw new \RuntimeException('slug_conflict', 0, $e);
            }

            throw $e;
        }
    }
}
