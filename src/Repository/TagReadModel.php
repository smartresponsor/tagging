<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Repository;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\RepositoryInterface\TagReadModelInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagReadModel implements TagReadModelInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /** @return array<int, array{id: string, slug: string, name: string, locale: ?string, weight: int}> */
    public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
    {
        $query = self::normalizedQuery($q);
        if ('' === $query) {
            return [];
        }

        $rows = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(TagEntity::class, 'e')
            ->where('e.tenant = :tenant')
            ->andWhere('LOWER(e.objectIdentity.slug) LIKE :query OR LOWER(e.objectTitle.firstTitle) LIKE :query')
            ->orderBy('e.weight', 'DESC')
            ->addOrderBy('e.objectTitle.firstTitle', 'ASC')
            ->setFirstResult(max(0, $offset))
            ->setMaxResults(max(1, $limit))
            ->setParameter('tenant', $tenant)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        return array_map(static fn(TagEntity $entity): array => self::mapTagSummary([
            'id' => $entity->id(),
            'slug' => $entity->slug(),
            'nameEntity' => $entity->nameEntity(),
            'locale' => $entity->locale(),
            'weight' => $entity->weight(),
        ]), $rows);
    }

    public function countSearch(string $tenant, string $q): int
    {
        $query = self::normalizedQuery($q);
        if ('' === $query) {
            return 0;
        }

        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(TagEntity::class, 'e')
            ->where('e.tenant = :tenant')
            ->andWhere('LOWER(e.objectIdentity.slug) LIKE :query OR LOWER(e.objectTitle.firstTitle) LIKE :query')
            ->setParameter('tenant', $tenant)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return array<int, array{slug: string, name: string}> */
    public function suggest(string $tenant, string $q, int $limit = 10): array
    {
        $query = self::normalizedQuery($q);
        if ('' === $query) {
            return [];
        }

        $rows = $this->entityManager->createQueryBuilder()
            ->select('e.objectIdentity.slug, e.objectTitle.firstTitle')
            ->from(TagEntity::class, 'e')
            ->where('e.tenant = :tenant')
            ->andWhere('LOWER(e.objectIdentity.slug) LIKE :prefix OR LOWER(e.objectTitle.firstTitle) LIKE :prefix')
            ->orderBy('e.weight', 'DESC')
            ->addOrderBy('e.objectTitle.firstTitle', 'ASC')
            ->setMaxResults(max(1, min(50, $limit)))
            ->setParameter('tenant', $tenant)
            ->setParameter('prefix', $query . '%')
            ->getQuery()
            ->getArrayResult();

        return array_map(self::mapSuggestItem(...), $rows);
    }

    /** @return array{slug: string, name: string} */
    private static function mapSuggestItem(array $row): array
    {
        return [
            'slug' => trim((string) ($row['slug'] ?? '')),
            'nameEntity' => trim((string) ($row['nameEntity'] ?? '')),
        ];
    }

    /** @return array{id: string, slug: string, name: string, locale: ?string, weight: int} */
    private static function mapTagSummary(array $row): array
    {
        return [
            'id' => trim((string) ($row['id'] ?? '')),
            'slug' => trim((string) ($row['slug'] ?? '')),
            'nameEntity' => trim((string) ($row['nameEntity'] ?? '')),
            'locale' => isset($row['locale']) && '' !== trim((string) $row['locale']) ? trim((string) $row['locale']) : null,
            'weight' => (int) ($row['weight'] ?? 0),
        ];
    }

    private static function normalizedQuery(string $query): string
    {
        return mb_strtolower(trim($query));
    }

    /** @return array<int, array{entity_type: string, entity_id: string}> */
    public function linksForTag(string $tenant, string $tagId, int $limit = 100): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('l.assignedType AS entity_type, l.assignedId AS entity_id')
            ->from(TagAssignmentEntity::class, 'l')
            ->where('l.tenant = :tenant')
            ->andWhere('l.tagId = :tagId')
            ->orderBy('l.assignedType', 'ASC')
            ->addOrderBy('l.assignedId', 'ASC')
            ->setMaxResults(max(1, $limit))
            ->setParameter('tenant', $tenant)
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn(array $row): array => [
                'entity_type' => trim((string) ($row['entity_type'] ?? '')),
                'entity_id' => trim((string) ($row['entity_id'] ?? '')),
            ],
            $rows,
        );
    }

    /** @return array<int, array{id: string, slug: string, name: string}> */
    public function tagsForEntity(string $tenant, string $etype, string $eid, int $limit = 100): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('e.id AS id, e.objectIdentity.slug AS slug, e.objectTitle.firstTitle AS nameEntity')
            ->from(TagAssignmentEntity::class, 'l')
            ->join(TagEntity::class, 'e', 'WITH', 'e.tenant = l.tenant AND e.id = l.tagId')
            ->where('l.tenant = :tenant')
            ->andWhere('l.assignedType = :etype')
            ->andWhere('l.assignedId = :eid')
            ->orderBy('e.weight', 'DESC')
            ->addOrderBy('e.objectTitle.firstTitle', 'ASC')
            ->setMaxResults(max(1, $limit))
            ->setParameter('tenant', $tenant)
            ->setParameter('etype', $etype)
            ->setParameter('eid', $eid)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn(array $row): array => [
                'id' => trim((string) ($row['id'] ?? '')),
                'slug' => trim((string) ($row['slug'] ?? '')),
                'nameEntity' => trim((string) ($row['nameEntity'] ?? '')),
            ],
            $rows,
        );
    }
}
