<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\Cache\Tag\SearchCache;
use App\Infra\Tag\TagReadModel;

/**
 *
 */

/**
 *
 */
final class SearchService
{
    /**
     * @param \App\Infra\Tag\TagReadModel $read
     * @param \App\Cache\Tag\SearchCache $cache
     */
    public function __construct(private readonly TagReadModel $read, private readonly SearchCache $cache)
    {
    }

    /** @return array{items:array<int,array<string,mixed>>, total:int, nextPageToken:?string, cacheHit:bool} */
    public function search(string $tenant, string $q, int $pageSize = 20, ?string $pageToken = null): array
    {
        $offset = 0;
        if ($pageToken) {
            $dec = base64_decode($pageToken, true);
            if ($dec !== false) {
                $off = (int)$dec;
                if ($off >= 0) $offset = $off;
            }
        }
        $c = $this->cache->get($tenant, $q, $pageSize, $offset);
        if ($c['hit'] ?? false) {
            $data = $c['data'] ?? ['items' => [], 'total' => 0, 'nextPageToken' => null];
            $data['cacheHit'] = true;
            return $data;
        }

        $items = $this->read->search($tenant, $q, $pageSize + 1, $offset);
        $hasNext = count($items) > $pageSize;
        if ($hasNext) array_pop($items);
        $next = $hasNext ? base64_encode((string)($offset + $pageSize)) : null;
        $res = ['items' => $items, 'total' => -1, 'nextPageToken' => $next, 'cacheHit' => false];
        $this->cache->set($tenant, $q, $pageSize, $offset, ['items' => $items, 'total' => -1, 'nextPageToken' => $next]);
        return $res;
    }
}
