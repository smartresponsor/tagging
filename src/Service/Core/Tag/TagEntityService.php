<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Service\Core\Tag\Record\TagEntityCreateRecord;
use App\Service\Core\Tag\Slug\SlugPolicy;

final readonly class TagEntityService implements TagEntityQueryServiceInterface
{
    public function __construct(
        private TagEntityRepositoryInterface $repo,
        private SlugPolicy $slugPolicy,
        private TagEntityPayloadNormalizer $normalizer = new TagEntityPayloadNormalizer(),
    ) {
    }

    /** @param array<string,mixed> $payload */
    public function create(string $tenant, array $payload): array
    {
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        $normalized = $this->normalizer->normalizeCreate(
            $payload,
            fn (string $name): string => $this->slugPolicy->make($tenant, $name),
        );
        if (!$this->slugPolicy->validate($normalized['slug'])) {
            throw new \InvalidArgumentException('validation_failed');
        }

        return $this->repo->create(
            $tenant,
            new TagEntityCreateRecord(
                $this->ulid(),
                $normalized['slug'],
                $normalized['name'],
                $normalized['locale'],
                $normalized['weight'],
            ),
        );
    }

    public function get(string $tenant, string $id): ?array
    {
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        return $this->repo->findById($tenant, $id);
    }

    /** @param array<string,mixed> $payload */
    public function patch(string $tenant, string $id, array $payload): void
    {
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        $patch = $this->normalizer->normalizePatch($payload);
        if ([] === $patch) {
            throw new \InvalidArgumentException('validation_failed');
        }

        $this->repo->patch($tenant, $id, $patch);
    }

    public function delete(string $tenant, string $id): void
    {
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        $this->repo->delete($tenant, $id);
    }

    /**
     * @throws \Random\RandomException
     */
    private function ulid(): string
    {
        return substr(strtoupper(bin2hex(random_bytes(13))), 0, 26);
    }
}
