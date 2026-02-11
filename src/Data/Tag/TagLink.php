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
final readonly class TagLink
{
    /**
     * @param string $tenant
     * @param string $entityType
     * @param string $entityId
     * @param string $tagId
     * @param \DateTimeImmutable|null $createdAt
     */
    public function __construct(
        private string             $tenant,
        private string             $entityType,   // category|product|project|text
        private string             $entityId,
        private string             $tagId,        // ULID (26)
        private ?DateTimeImmutable $createdAt = null,
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
    public function entityType(): string
    {
        return $this->entityType;
    }

    /**
     * @return string
     */
    public function entityId(): string
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function tagId(): string
    {
        return $this->tagId;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
