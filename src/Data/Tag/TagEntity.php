<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Data\Tag;

use DateTimeImmutable;

/**
 *
 */

/**
 *
 */
final class TagEntity
{
    /**
     * @param string $tenant
     * @param string $id
     * @param string $slug
     * @param string $name
     * @param string|null $locale
     * @param int $weight
     * @param \DateTimeImmutable|null $createdAt
     * @param \DateTimeImmutable|null $updatedAt
     */
    public function __construct(
        private readonly string             $tenant,
        private readonly string             $id,        // ULID (26)
        private readonly string             $slug,
        private readonly string             $name,
        private readonly ?string            $locale = null,
        private readonly int                $weight = 0,
        private readonly ?DateTimeImmutable $createdAt = null,
        private readonly ?DateTimeImmutable $updatedAt = null,
    )
    {
    }

    /**
     * @return string
     */
    public function tenant(): string
    {
        return $this->tenant;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function locale(): ?string
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function weight(): int
    {
        return $this->weight;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
