<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

interface AssignOperationInterface
{
    /** @return array{ok:bool, duplicated?:bool, conflict?:bool, code?:string} */
    public function assign(
        string $tenant,
        string $tagId,
        string $entityType,
        string $entityId,
        ?string $idemKey = null,
    ): array;
}
