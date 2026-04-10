<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Cache\Store\Tag\SuggestCache;

/**
 * Host-minimal suggest read service backed by the tag read-model.
 */
final readonly class SuggestService
{
    public function __construct(private TagReadModelInterface $read, private SuggestCache $cache)
    {
    }

    /**
     * @return array{items:array<int,array{slug:string,name:string}>, cacheHit:bool}
     *
     * @throws \JsonException
     */
    public function suggest(string $tenant, string $q, int $limit = 10): array
    {
        $q = trim($q);
        $limit = max(1, min(50, $limit));
        if ('' === $q) {
            return ['items' => [], 'cacheHit' => false];
        }

        $c = $this->cache->get($tenant, $q, $limit);
        if ($c['hit'] ?? false) {
            $data = $c['data'] ?? ['items' => []];
            $data['cacheHit'] = true;

            return $data;
        }

        $items = $this->read->suggest($tenant, $q, $limit);
        $res = ['items' => $items, 'cacheHit' => false];
        $this->cache->set($tenant, $q, $limit, ['items' => $items]);

        return $res;
    }
}
