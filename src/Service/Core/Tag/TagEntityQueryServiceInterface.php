<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

interface TagEntityQueryServiceInterface
{
    /** @return array{id:string,slug:string,name:string,locale:string,weight:int,created_at?:string,updated_at?:string}|null */
    public function get(string $tenant, string $id): ?array;
}
