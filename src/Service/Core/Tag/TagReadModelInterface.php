<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

/**
 * Host-minimal read-model contract for assignment reads, search, and suggest.
 */
interface TagReadModelInterface
{
    /** @return array<int, array{id:string,slug:string,name:string,locale:?string,weight:int}> */
    public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array;

    /** @return array<int, array{slug:string,name:string}> */
    public function suggest(string $tenant, string $q, int $limit = 10): array;

    /** @return array<int, array{entity_type:string,entity_id:string}> */
    public function linksForTag(string $tenant, string $tagId, int $limit = 100): array;

    /** @return array<int, array{id:string,slug:string,name:string}> */
    public function tagsForEntity(string $tenant, string $etype, string $eid, int $limit = 100): array;
}
