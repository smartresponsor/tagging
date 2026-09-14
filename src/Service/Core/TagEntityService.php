<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\Slug\TagSlugPolicy;
use Random\RandomException;

final readonly class TagEntityService implements TagEntityQueryServiceInterface
{
    public function __construct(
        private TagCrudRepositoryInterface $repo,
        private TagSlugPolicy $slugPolicy,
        private TagEntityPayloadNormalizer $normalizer = new TagEntityPayloadNormalizer(),
    ) {}

    /**
     * @param array<string,mixed> $payload
     *
     * @throws RandomException
     */
    public function create(string $tenant, array $payload): array
    {
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        $normalized = $this->normalizer->normalizeCreate(
            $payload,
            fn(string $nameEntity): string => $this->slugPolicy->make($tenant, $nameEntity),
        );
        if (!$this->slugPolicy->validate($normalized['slug'])) {
            throw new \InvalidArgumentException('validation_failed');
        }

        return $this->repo->create(
            $tenant,
            new TagEntityCreateRecord(
                $this->ulid(),
                $normalized['slug'],
                $normalized['nameEntity'],
                $normalized['locale'],
                $normalized['weight'],
            ),
        );
    }

    /** @return list<array<string, mixed>> */
    public function index(string $tenant, int $limit = 100, int $offset = 0): array
    {
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        $entities = $this->repo->listAllTags($tenant);
        $entities = array_values(array_filter($entities, static fn($entity): bool => $entity instanceof \App\Tagging\Entity\Tag\TagEntity));

        return array_map(
            fn(\App\Tagging\Entity\Tag\TagEntity $entity): array => $this->toArray($entity),
            array_slice($entities, max(0, $offset), max(1, $limit)),
        );
    }

    public function findById(string $tenant, string $id): ?array
    {
        return $this->repo->findById($tenant, $id);
    }

    public function findBySlug(string $tenant, string $slug): ?array
    {
        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        $entity = $this->repo->getBySlug($tenant, $slug);

        return $entity instanceof \App\Tagging\Entity\Tag\TagEntity ? $this->toArray($entity) : null;
    }

    public function get(string $tenant, string $id): ?array
    {
        return $this->findById($tenant, $id);
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
     * @throws RandomException
     */
    private function ulid(): string
    {
        return substr(strtoupper(bin2hex(random_bytes(13))), 0, 26);
    }

    private function toArray(\App\Tagging\Entity\Tag\TagEntity $entity): array
    {
        return [
            'id' => $entity->id(),
            'slug' => $entity->slug(),
            'nameEntity' => $entity->label(),
            'locale' => (string) ($entity->locale() ?? ''),
            'weight' => $entity->weight(),
            'required_flag' => $entity->requiredFlag(),
            'mod_only_flag' => $entity->modOnlyFlag(),
            'created_at' => $entity->createdAt()->format(DATE_ATOM),
            'updated_at' => $entity->updatedAt()?->format(DATE_ATOM) ?? '',
        ];
    }
}
