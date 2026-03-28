<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Cache\Store\Tag\SearchCache;

final readonly class SearchService
{
    /**
     * @param \App\Infrastructure\ReadModel\Tag\TagReadModelInterface $read
     */
    public function __construct(private TagReadModelInterface $read, private SearchCache $cache) {}

    /** @return array{items:array<int,array<string,mixed>>, total:int, nextPageToken: ?string, cacheHit:bool} */
    public function search(string $tenant, string $q, int $pageSize = 20, ?string $pageToken = null): array
    {
        $q = trim($q);
        $pageSize = $this->normalizePageSize($pageSize);
        if ('' === $q) {
            return $this->emptyResult();
        }
        $offset = $this->decodeOffset($pageToken);
        $cacheEntry = $this->cache->get($tenant, $q, $pageSize, $offset);
        if ($cacheEntry['hit'] ?? false) {
            $data = $cacheEntry['data'] ?? $this->emptyResult();
            $data['cacheHit'] = true;

            return $data;
        }

        $items = $this->read->search($tenant, $q, $pageSize + 1, $offset);
        $hasNext = count($items) > $pageSize;
        if ($hasNext) {
            array_pop($items);
        }
        $result = [
            'items' => $items,
            'total' => -1,
            'nextPageToken' => $hasNext ? base64_encode((string) ($offset + $pageSize)) : null,
            'cacheHit' => false,
        ];
        $this->cache->set($tenant, $q, $pageSize, $offset, [
            'items' => $result['items'],
            'total' => $result['total'],
            'nextPageToken' => $result['nextPageToken'],
        ]);

        return $result;
    }

    private function normalizePageSize(int $pageSize): int
    {
        return max(1, min(100, $pageSize));
    }

    private function decodeOffset(?string $pageToken): int
    {
        if (null === $pageToken || '' === $pageToken) {
            return 0;
        }

        $decoded = base64_decode($pageToken, true);
        if (false === $decoded) {
            return 0;
        }

        return max(0, (int) $decoded);
    }

    /** @return array{items:array<int,array<string,mixed>>, total:int, nextPageToken:?string, cacheHit:bool} */
    private function emptyResult(): array
    {
        return ['items' => [], 'total' => 0, 'nextPageToken' => null, 'cacheHit' => false];
    }
}
