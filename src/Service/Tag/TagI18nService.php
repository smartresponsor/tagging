<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

final class TagI18nService {
    public function __construct(private TagRepositoryContract $repo){}
    public function putLabel(string $tenantId, string $tagId, string $locale, string $label): void {
        $this->repo->putLabel($tenantId, UlidGenerator::generate(), $tagId, strtolower($locale), $label);
    }
    public function listLabels(string $tenantId, string $tagId): array { return $this->repo->listLabels($tenantId, $tagId); }
    public function putSlug(string $tenantId, string $tagId, string $locale, string $labelOrSlug): void {
        $slug = TagNormalizer::slugify($labelOrSlug);
        $this->repo->putSlugI18n($tenantId, UlidGenerator::generate(), $tagId, strtolower($locale), $slug);
    }
    public function getSlug(string $tenantId, string $tagId, string $locale): ?string {
        return $this->repo->getSlugI18n($tenantId, $tagId, strtolower($locale));
    }
}
