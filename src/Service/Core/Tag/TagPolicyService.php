<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Service\Core\Tag\TagRepositoryInterface as TagRepositoryContract;

final readonly class TagPolicyService
{
    /** @var string[] */
    private array $allowedPrefixes;

    /** @var string[] */
    private array $deniedPrefixes;

    /** @var string[] */
    private array $allowedRegex;

    /** @var string[] */
    private array $deniedRegex;

    public function __construct(
        private TagValidator $validator,
        private array $cfg, // from config/tag_policy.yaml
    ) {
        $this->allowedPrefixes = $this->stringList('allowed_prefixes');
        $this->deniedPrefixes = $this->stringList('denied_prefixes');
        $this->allowedRegex = $this->stringList('allowed_regex');
        $this->deniedRegex = $this->stringList('denied_regex');
    }

    public function normalizeSlug(string $input): string
    {
        $s = $this->validator->normalizeSlug($input);
        if (!empty($this->cfg['normalize']['lowercase'])) {
            $s = strtolower($s);
        }

        return $s;
    }

    public function slugForLabel(string $label): string
    {
        $this->validator->validateLabel($label);

        $slug = $this->normalizeSlug($label);
        $this->applyRules($slug);
        $this->validator->validateSlug($slug);

        return $slug;
    }

    public function validateBeforeCreate(
        string $tenantId,
        TagRepositoryContract $repo,
        string $label,
        ?string $slug = null,
    ): void {
        $slug = null !== $slug && '' !== $slug ? $this->normalizeSlug($slug) : $this->slugForLabel($label);
        $this->applyRules($slug);
        $this->validator->validateSlug($slug);
        $this->validator->ensureUniqueness($tenantId, $repo, $slug);
    }

    public function validateBeforeUpdate(
        string $tenantId,
        TagRepositoryContract $repo,
        string $tagId,
        string $label,
        ?string $slug = null,
    ): void {
        $this->validator->validateLabel($label);
        if (null !== $slug) {
            $slug = $this->normalizeSlug($slug);
            $this->applyRules($slug);
            $this->validator->validateSlug($slug);
            $this->validator->ensureUniqueness($tenantId, $repo, $slug, $tagId);
        }
    }

    private function applyRules(string $slug): void
    {
        if (in_array($slug, $this->cfg['reserved_slugs'] ?? [], true)) {
            throw new \InvalidArgumentException('slug_reserved');
        }

        if ([] !== $this->allowedPrefixes && !$this->matchesAllowedPrefix($slug)) {
            throw new \InvalidArgumentException('slug_prefix_not_allowed');
        }

        foreach ($this->deniedPrefixes as $prefix) {
            if ('' !== $prefix && str_starts_with($slug, $prefix)) {
                throw new \InvalidArgumentException('slug_denied_prefix');
            }
        }

        if ([] !== $this->allowedRegex && !$this->matchesAllowedRegex($slug)) {
            throw new \InvalidArgumentException('slug_regex_not_allowed');
        }

        foreach ($this->deniedRegex as $pattern) {
            if ($this->matchesPattern($pattern, $slug)) {
                throw new \InvalidArgumentException('slug_denied_regex');
            }
        }
    }

    private function matchesAllowedPrefix(string $slug): bool
    {
        foreach ($this->allowedPrefixes as $prefix) {
            if ('' === $prefix || str_starts_with($slug, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function matchesAllowedRegex(string $slug): bool
    {
        foreach ($this->allowedRegex as $pattern) {
            if ($this->matchesPattern($pattern, $slug)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPattern(string $pattern, string $slug): bool
    {
        if ('' === $pattern) {
            return false;
        }

        $match = @preg_match('/'.$pattern.'/', $slug);

        return 1 === $match;
    }

    /** @return string[] */
    private function stringList(string $key): array
    {
        $items = $this->cfg[$key] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            $normalized[] = trim((string) $item);
        }

        return array_values(array_unique($normalized));
    }
}
