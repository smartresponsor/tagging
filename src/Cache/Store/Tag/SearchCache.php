<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Cache\Store\Tag;

final readonly class SearchCache
{
    private TagFileCacheStore $store;

    public function __construct(private string $dir = 'var/cache/tag-search', private int $ttl = 60)
    {
        $this->store = new TagFileCacheStore($this->dir, $this->ttl);
    }

    public function get(string $tenant, string $q, int $limit, int $offset): array
    {
        return $this->store->get('search', $tenant, [$q, $limit, $offset]);
    }

    public function clearTenant(string $tenant): void
    {
        $this->store->clearTenant('search', $tenant);
    }

    /** @param array<string,mixed> $data */
    public function set(string $tenant, string $q, int $limit, int $offset, array $data): void
    {
        $this->store->set('search', $tenant, [$q, $limit, $offset], $data);
    }
}
