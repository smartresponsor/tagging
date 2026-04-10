<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Service\Core\Tag\Record\TagEntityCreateRecord;

interface TagEntityRepositoryInterface
{
    /**
     * @return array{
     *     id: string,
     *     slug: string,
     *     name: string,
     *     locale: string,
     *     weight: int,
     *     created_at?: string,
     *     updated_at?: string
     * }|null
     */
    public function findById(string $tenant, string $id): ?array;

    /** @return array{id:string,slug:string,name:string,locale:string,weight:int} */
    public function create(string $tenant, TagEntityCreateRecord $record): array;

    /** @param array{name?:string,locale?:string,weight?:int} $patch */
    public function patch(string $tenant, string $id, array $patch): void;

    public function delete(string $tenant, string $id): void;
}
