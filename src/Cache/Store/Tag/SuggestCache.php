<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Cache\Store\Tag;

final readonly class SuggestCache
{
    private TagFileCacheStore $store;

    public function __construct(private string $dir = 'var/cache/tag-suggest', private int $ttl = 60)
    {
        $this->store = new TagFileCacheStore($this->dir, $this->ttl);
    }

    public function get(string $tenant, string $q, int $limit): array
    {
        return $this->store->get('suggest', $tenant, [$q, $limit]);
    }

    public function clearTenant(string $tenant): void
    {
        $this->store->clearTenant('suggest', $tenant);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws \JsonException
     */
    public function set(string $tenant, string $q, int $limit, array $data): void
    {
        $this->store->set('suggest', $tenant, [$q, $limit], $data);
    }
}
