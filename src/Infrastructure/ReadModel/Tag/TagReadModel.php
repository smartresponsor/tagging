<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\ReadModel\Tag;

use App\Service\Core\Tag\TagReadModelInterface;

final readonly class TagReadModel implements TagReadModelInterface
{
    public function __construct(private \PDO $pdo) {}

    /** @return array<int, array{id:string,slug:string,name:string,locale:?string,weight:int}> */
    public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
    {
        $query = self::normalizedQuery($q);
        if ('' == $query) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, slug, name, locale, weight
'
            . 'FROM tag_entity
'
            . 'WHERE tenant = :t AND (slug ILIKE :q OR name ILIKE :q)
'
            . 'ORDER BY weight DESC, name ASC
'
            . 'LIMIT :l OFFSET :o',
        );
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':q', '%' . $query . '%');
        $stmt->bindValue(':l', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':o', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return array_map(self::mapTagSummary(...), $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: []);
    }

    public function countSearch(string $tenant, string $q): int
    {
        $query = self::normalizedQuery($q);
        if ('' === $query) {
            return 0;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
'
            . 'FROM tag_entity
'
            . 'WHERE tenant = :t AND (slug ILIKE :q OR name ILIKE :q)',
        );
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':q', '%' . $query . '%');
        $stmt->execute();

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    /** @return array<int, array{slug:string,name:string}> */
    public function suggest(string $tenant, string $q, int $limit = 10): array
    {
        $query = self::normalizedQuery($q);
        if ('' == $query) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT slug, name
'
            . 'FROM tag_entity
'
            . 'WHERE tenant = :t AND (slug ILIKE :pfx OR name ILIKE :pfx)
'
            . 'ORDER BY weight DESC, name ASC
'
            . 'LIMIT :l',
        );
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':pfx', $query . '%');
        $stmt->bindValue(':l', max(1, min(50, $limit)), \PDO::PARAM_INT);
        $stmt->execute();

        return array_map(self::mapSuggestItem(...), $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: []);
    }

    /** @return array{slug:string,name:string} */
    private static function mapSuggestItem(array $row): array
    {
        return [
            'slug' => (string) ($row['slug'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
        ];
    }

    /** @return array{id:string,slug:string,name:string,locale:?string,weight:int} */
    private static function mapTagSummary(array $row): array
    {
        return [
            'id' => (string) ($row['id'] ?? ''),
            'slug' => (string) ($row['slug'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'locale' => isset($row['locale']) && '' !== $row['locale'] ? (string) $row['locale'] : null,
            'weight' => (int) ($row['weight'] ?? 0),
        ];
    }

    private static function normalizedQuery(string $query): string
    {
        return mb_strtolower(trim($query));
    }

    /** @return array<int, array{entity_type:string,entity_id:string}> */
    public function linksForTag(string $tenant, string $tagId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT entity_type, entity_id
             FROM tag_link
             WHERE tenant = :t AND tag_id = :id
             ORDER BY entity_type, entity_id
             LIMIT :l',
        );
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':id', $tagId);
        $stmt->bindValue(':l', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
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
             LIMIT :l',
        );
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':et', $etype);
        $stmt->bindValue(':eid', $eid);
        $stmt->bindValue(':l', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
