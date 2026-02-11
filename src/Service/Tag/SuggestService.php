<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\Cache\Tag\SuggestCache;
use PDO;

/**
 *
 */

/**
 *
 */
final class SuggestService
{
    /**
     * @param \PDO $pdo
     * @param \App\Cache\Tag\SuggestCache $cache
     */
    public function __construct(private readonly PDO $pdo, private readonly SuggestCache $cache)
    {
    }

    /** @return array{items:array<int,array{slug:string,name:string}>, cacheHit:bool} */
    public function suggest(string $tenant, string $q, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $c = $this->cache->get($tenant, $q, $limit);
        if ($c['hit'] ?? false) {
            $data = $c['data'] ?? ['items' => []];
            $data['cacheHit'] = true;
            return $data;
        }

        $stmt = $this->pdo->prepare(
            'SELECT slug, name FROM tag_entity
             WHERE tenant=:t AND (slug ILIKE :pfx OR name ILIKE :pfx)
             ORDER BY weight DESC, name ASC LIMIT :l'
        );
        $pfx = strtolower(trim($q)) . '%';
        $stmt->bindValue(':t', $tenant);
        $stmt->bindValue(':pfx', $pfx);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $res = ['items' => $items, 'cacheHit' => false];
        $this->cache->set($tenant, $q, $limit, ['items' => $items]);
        return $res;
    }
}
