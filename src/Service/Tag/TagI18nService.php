<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

/**
 *
 */

/**
 *
 */
final class TagI18nService
{
    /**
     * @param \App\ServiceInterface\Tag\TagRepositoryInterface $repo
     */
    public function __construct(private readonly TagRepositoryContract $repo)
    {
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $locale
     * @param string $label
     * @return void
     * @throws \Random\RandomException
     */
    public function putLabel(string $tenantId, string $tagId, string $locale, string $label): void
    {
        $this->repo->putLabel($tenantId, UlidGenerator::generate(), $tagId, strtolower($locale), $label);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array
     */
    public function listLabels(string $tenantId, string $tagId): array
    {
        return $this->repo->listLabels($tenantId, $tagId);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $locale
     * @param string $labelOrSlug
     * @return void
     * @throws \Random\RandomException
     */
    public function putSlug(string $tenantId, string $tagId, string $locale, string $labelOrSlug): void
    {
        $slug = TagNormalizer::slugify($labelOrSlug);
        $this->repo->putSlugI18n($tenantId, UlidGenerator::generate(), $tagId, strtolower($locale), $slug);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $locale
     * @return string|null
     */
    public function getSlug(string $tenantId, string $tagId, string $locale): ?string
    {
        return $this->repo->getSlugI18n($tenantId, $tagId, strtolower($locale));
    }
}
