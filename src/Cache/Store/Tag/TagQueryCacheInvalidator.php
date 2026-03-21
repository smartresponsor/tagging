<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Cache\Store\Tag;

final readonly class TagQueryCacheInvalidator
{
    public function __construct(
        private ?SearchCache $searchCache = null,
        private ?SuggestCache $suggestCache = null,
    ) {
    }

    public function clearTenant(string $tenant): void
    {
        $this->searchCache?->clearTenant($tenant);
        $this->suggestCache?->clearTenant($tenant);
    }
}
