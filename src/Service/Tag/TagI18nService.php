<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

final class TagI18nService {
    public function __construct(private TagRepositoryContract $repo){}
    public function putLabel(string $tagId, string $locale, string $label): void {
        $this->repo->putLabel(UlidGenerator::generate(), $tagId, strtolower($locale), $label);
    }
    public function listLabels(string $tagId): array { return $this->repo->listLabels($tagId); }
    public function putSlug(string $tagId, string $locale, string $labelOrSlug): void {
        $slug = TagNormalizer::slugify($labelOrSlug);
        $this->repo->putSlugI18n(UlidGenerator::generate(), $tagId, strtolower($locale), $slug);
    }
    public function getSlug(string $tagId, string $locale): ?string {
        return $this->repo->getSlugI18n($tagId, strtolower($locale));
    }
}
