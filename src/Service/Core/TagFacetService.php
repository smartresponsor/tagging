<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\RepositoryInterface\TagRepositoryInterface as TagRepositoryContract;

final readonly class TagFacetService
{
    public function __construct(private TagRepositoryContract $repo) {}

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function topByType(string $vendorId, string $assignedType, int $limit = 50): array
    {
        return $this->repo->facetTop($vendorId, $assignedType, $limit);
    }

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function cloud(string $vendorId, int $limit = 100): array
    {
        return $this->repo->tagCloud($vendorId, $limit);
    }
}
