<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

interface UnassignOperationInterface
{
    /**
     * Returns `code=tag_not_found` when the tag entity itself does not exist.
     * Returns `ok=true, not_found=true` when the tag exists but the entity link is absent.
     *
     * @return array{ok:bool, duplicated?:bool, not_found?:bool, conflict?:bool, code?:string}
     */
    public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array;
}
