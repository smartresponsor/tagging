<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Slug\Tag;

use App\Service\Core\Tag\Slug\SlugPolicy as CoreSlugPolicy;

/**
 * Backward-compatible facade over the canonical core slug policy.
 */
final class SlugPolicy
{
    private CoreSlugPolicy $inner;

    /** @param list<string> $reserved */
    public function __construct(
        \PDO $pdo,
        Slugifier $slugifier,
        array $reserved = [],
        int $maxLen = 64,
    ) {
        $this->inner = new CoreSlugPolicy($pdo, $slugifier->core(), $reserved, $maxLen);
    }

    public function make(string $tenant, string $source): string
    {
        return $this->inner->make($tenant, $source);
    }

    public function validate(string $slug): bool
    {
        return $this->inner->validate($slug);
    }
}
