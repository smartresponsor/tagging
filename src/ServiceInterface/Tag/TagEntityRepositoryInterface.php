<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

interface TagEntityRepositoryInterface
{
    /** @return array{id:string,slug:string,name:string,locale:string,weight:int,created_at?:string,updated_at?:string}|null */
    public function findById(string $tenant, string $id): ?array;

    /** @return array{id:string,slug:string,name:string,locale:string,weight:int} */
    public function create(string $tenant, string $id, string $slug, string $name, string $locale, int $weight): array;

    /** @param array{name?:string,locale?:string,weight?:int} $patch */
    public function patch(string $tenant, string $id, array $patch): void;

    public function delete(string $tenant, string $id): void;
}
