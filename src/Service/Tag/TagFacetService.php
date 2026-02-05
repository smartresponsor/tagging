<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

final class TagFacetService {
    public function __construct(private TagRepositoryContract $repo){}
    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function topByType(string $tenantId, string $assignedType, int $limit = 50): array { return $this->repo->facetTop($tenantId, $assignedType, $limit); }
    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function cloud(string $tenantId, int $limit = 100): array { return $this->repo->tagCloud($tenantId, $limit); }
}
