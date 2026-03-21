<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

interface UnassignOperationInterface
{
    /** @return array{ok:bool, duplicated?:bool, not_found?:bool, conflict?:bool, code?:string} */
    public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array;
}
