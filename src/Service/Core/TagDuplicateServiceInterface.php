<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core;

interface TagDuplicateServiceInterface
{
    /** @param array<string,mixed> $override @return array<string,mixed> */
    public function duplicate(string $tenant, ?string $id, ?string $slug, array $override = []): array;
}
