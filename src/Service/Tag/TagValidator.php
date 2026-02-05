<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

final class TagValidator
{
    private const MAX_LEN = 255;
    /** @var string[] */
    private array $reserved = [];

    public function normalizeSlug(string $input): string
    {
        // Lowercase, trim, collapse spaces/underscores to hyphens, strip unsafe chars
        $s = strtolower(trim($input));
        $s = preg_replace('/[^a-z0-9\-\s_]+/', '', $s) ?? '';
        $s = preg_replace('/[\s_]+/', '-', $s) ?? '';
        return trim($s, '-');
    }

    public function validateLabel(string $label): void
    {
        $len = mb_strlen($label);
        if ($len === 0 || $len > self::MAX_LEN) {
            throw new \InvalidArgumentException('label_length_invalid');
        }
    }

    public function validateSlug(string $slug): void
    {
        $len = mb_strlen($slug);
        if ($len === 0 || $len > self::MAX_LEN) {
            throw new \InvalidArgumentException('slug_length_invalid');
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new \InvalidArgumentException('slug_format_invalid');
        }
        if (in_array($slug, $this->reserved, true)) {
            throw new \InvalidArgumentException('slug_reserved');
        }
    }

    /**
     * Check uniqueness via repository lookups.
     * Repository must provide 'existsSlug' and 'i18nSlugExists' checks.
     */
    public function ensureUniqueness(string $tenantId, TagRepositoryContract $repo, string $slug, ?string $tagId = null): void
    {
        if ($repo->existsSlug($tenantId, $slug, $tagId)) {
            throw new \RuntimeException('slug_conflict');
        }
    }
}
