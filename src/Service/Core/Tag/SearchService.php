<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Cache\Search\Tag\SearchCache;
use App\Infrastructure\ReadModel\Tag\TagReadModel;

final readonly class SearchService
{
    public function __construct(private TagReadModel $read, private SearchCache $cache)
    {
    }

    /** @return array{items:array<int,array<string,mixed>>, total:int, nextPageToken: ?string, cacheHit:bool} */
    public function search(string $tenant, string $q, int $pageSize = 20, ?string $pageToken = null): array
    {
        $pageSize = max(1, min(100, $pageSize));
        $offset = 0;
        if ($pageToken) {
            $dec = base64_decode($pageToken, true);
            if (false !== $dec) {
                $off = (int) $dec;
                if ($off >= 0) {
                    $offset = $off;
                }
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
        if ($hasNext) {
            array_pop($items);
        }
        $next = $hasNext ? base64_encode((string) ($offset + $pageSize)) : null;
        $res = ['items' => $items, 'total' => -1, 'nextPageToken' => $next, 'cacheHit' => false];
        $this->cache->set($tenant, $q, $pageSize, $offset, ['items' => $items, 'total' => -1, 'nextPageToken' => $next]);

        return $res;
    }
}
