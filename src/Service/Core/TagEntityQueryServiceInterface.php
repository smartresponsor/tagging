<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core;

interface TagEntityQueryServiceInterface
{
    /** @return list<array<string,mixed>> */
    public function index(string $tenant, int $limit = 100, int $offset = 0): array;

    /** @return array<string,mixed>|null */
    public function findById(string $tenant, string $id): ?array;

    /** @return array<string,mixed>|null */
    public function findBySlug(string $tenant, string $slug): ?array;
}
