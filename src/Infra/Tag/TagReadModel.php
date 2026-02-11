<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Tag;

use Locale;
use PDO;

/**
 *
 */

/**
 *
 */
final readonly class TagReadModel
{
    /**
     * @param \PDO $pdo
     */
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int, array{id:string,slug:string,name:string,locale:?string,weight:int}> */
    public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
    {
        // Portable LIKE-based search; Postgres fast path uses pg_trgm via API/DAO level.
        $stmt = $this->pdo->prepare(
            'SELECT id, slug, name, locale, weight
             FROM tag_entity
             WHERE tenant = :t AND (slug ILIKE :q OR name ILIKE :q)
             ORDER BY weight DESC, name ASC
             LIMIT :l OFFSET :o'
        );
        $like = '%' . $q . '%';
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':q', $like);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array{entity_type:string,entity_id:string}> */
    public function linksForTag(string $tenant, string $tagId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT entity_type, entity_id
             FROM tag_link
             WHERE tenant = :t AND tag_id = :id
             ORDER BY entity_type, entity_id
             LIMIT :l'
        );
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':id', $tagId);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array{id:string,slug:string,name:string}> */
    public function tagsForEntity(string $tenant, string $etype, string $eid, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id, e.slug, e.name
             FROM tag_link l
             JOIN tag_entity e ON e.tenant = l.tenant AND e.id = l.tag_id
             WHERE l.tenant = :t AND l.entity_type = :et AND l.entity_id = :eid
             ORDER BY e.weight DESC, e.name ASC
             LIMIT :l'
        );
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':et', $etype);
        $stmt->bindValue(':eid', $eid);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
