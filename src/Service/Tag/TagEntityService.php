<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\Service\Tag\Slug\SlugPolicy;
use App\ServiceInterface\Tag\TagEntityRepositoryInterface;
use InvalidArgumentException;

/**
 *
 */

/**
 *
 */
final readonly class TagEntityService
{
    /**
     * @param \App\ServiceInterface\Tag\TagEntityRepositoryInterface $repo
     * @param \App\Service\Tag\Slug\SlugPolicy $slugPolicy
     */
    public function __construct(private TagEntityRepositoryInterface $repo, private SlugPolicy $slugPolicy)
    {
    }

    /**
     * @param string $tenant
     * @param array<string,mixed> $payload
     * @return array
     * @throws \Random\RandomException
     */
    public function create(string $tenant, array $payload): array
    {
        if ($tenant === '') {
            throw new InvalidArgumentException('invalid_tenant');
        }

        $name = trim((string)($payload['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('validation_failed');
        }

        $slug = trim((string)($payload['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->slugPolicy->make($tenant, $name);
        }
        if (!$this->slugPolicy->validate($slug)) {
            throw new InvalidArgumentException('validation_failed');
        }

        $locale = (string)($payload['locale'] ?? 'en');
        $weight = (int)($payload['weight'] ?? 0);
        $id = $this->ulid();

        return $this->repo->create($tenant, $id, $slug, $name, $locale, $weight);
    }

    /**
     * @param string $tenant
     * @param string $id
     * @return array|null
     */
    public function get(string $tenant, string $id): ?array
    {
        if ($tenant === '') {
            throw new InvalidArgumentException('invalid_tenant');
        }
        return $this->repo->findById($tenant, $id);
    }

    /** @param array<string,mixed> $payload */
    public function patch(string $tenant, string $id, array $payload): void
    {
        if ($tenant === '') {
            throw new InvalidArgumentException('invalid_tenant');
        }
        $this->repo->patch($tenant, $id, $payload);
    }

    /**
     * @param string $tenant
     * @param string $id
     * @return void
     */
    public function delete(string $tenant, string $id): void
    {
        if ($tenant === '') {
            throw new InvalidArgumentException('invalid_tenant');
        }
        $this->repo->delete($tenant, $id);
    }

    /**
     * @return string
     * @throws \Random\RandomException
     */
    private function ulid(): string
    {
        return substr(strtoupper(bin2hex(random_bytes(13))), 0, 26);
    }
}
