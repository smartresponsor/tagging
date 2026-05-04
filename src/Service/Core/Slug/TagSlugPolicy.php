<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core\Slug;

use App\Tagging\Service\Core\TagEntityRepositoryInterface;

final readonly class TagSlugPolicy
{
    /** @param list<string> $reserved */
    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private TagSlugifier $slugifier,
        private array $reserved = [],
        private int $maxLen = 64,
    ) {}

    public function make(string $tenant, string $source): string
    {
        $base = $this->baseSlug($source);

        return $this->nextAvailableSlug($tenant, $base);
    }

    public function validate(string $slug): bool
    {
        return $this->hasValidLength($slug)
            && 1 === preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
            && !$this->isReserved($slug);
    }

    private function isReserved(string $slug): bool
    {
        return in_array($slug, $this->reserved, true);
    }

    private function hasValidLength(string $slug): bool
    {
        $length = strlen($slug);

        return $length >= 2 && $length <= $this->maxLen;
    }

    private function baseSlug(string $source): string
    {
        $slug = $this->slugifier->slugify($source);
        if ('' === $slug) {
            $slug = 'tag';
        }

        return $this->isReserved($slug) ? $slug . '-x' : $slug;
    }

    private function nextAvailableSlug(string $tenant, string $base): string
    {
        $slug = $base;
        $counter = 1;
        while ($this->exists($tenant, $slug)) {
            ++$counter;
            $slug = $this->suffixSlug($base, (string) $counter);
        }

        return $slug;
    }

    private function suffixSlug(string $base, string $suffix): string
    {
        $cut = max(1, $this->maxLen - (1 + strlen($suffix)));

        return substr($base, 0, $cut) . '-' . $suffix;
    }

    private function exists(string $tenant, string $slug): bool
    {
        return $this->repo->existsSlug($tenant, $slug);
    }
}
