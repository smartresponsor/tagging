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
final class TagLink
{
    /**
     * @param string $tenant
     * @param string $entityType
     * @param string $entityId
     * @param string $tagId
     * @param \DateTimeImmutable|null $createdAt
     */
    public function __construct(
        private readonly string             $tenant,
        private readonly string             $entityType,   // category|product|project|text
        private readonly string             $entityId,
        private readonly string             $tagId,        // ULID (26)
        private readonly ?DateTimeImmutable $createdAt = null,
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
