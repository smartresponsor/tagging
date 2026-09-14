<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core;

interface TagLifecycleServiceInterface
{
    /** @return array<string,mixed> */
    public function archive(string $tenant, ?string $id, ?string $slug): array;
    /** @return array<string,mixed> */
    public function restore(string $tenant, ?string $id, ?string $slug): array;
}
