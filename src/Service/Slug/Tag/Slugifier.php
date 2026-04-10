<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Slug\Tag;

use App\Service\Core\Tag\Slug\Slugifier as CoreSlugifier;

/**
 * Backward-compatible facade over the canonical core slugifier.
 */
final class Slugifier
{
    private CoreSlugifier $inner;

    public function __construct(
        bool $lowercase = true,
        int $maxLen = 64,
    ) {
        $this->inner = new CoreSlugifier($lowercase, $maxLen);
    }

    public function slugify(string $value): string
    {
        return $this->inner->slugify($value);
    }

    public function core(): CoreSlugifier
    {
        return $this->inner;
    }
}
