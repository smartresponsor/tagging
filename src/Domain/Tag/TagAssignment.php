<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Domain\Tag;

use DateTimeImmutable;

/**
 *
 */

/**
 *
 */
final readonly class TagAssignment
{
    /**
     * @param string $id
     * @param string $tagId
     * @param string $assignedType
     * @param string $assignedId
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        private string            $id,
        private string            $tagId,
        private string            $assignedType,
        private string            $assignedId,
        private DateTimeImmutable $createdAt
    )
    {
    }

    /**
     * @param string $id
     * @param string $tagId
     * @param string $assignedType
     * @param string $assignedId
     * @return self
     */
    public static function create(string $id, string $tagId, string $assignedType, string $assignedId): self
    {
        return new self($id, $tagId, $assignedType, $assignedId, new DateTimeImmutable());
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
    public function tagId(): string
    {
        return $this->tagId;
    }

    /**
     * @return string
     */
    public function assignedType(): string
    {
        return $this->assignedType;
    }

    /**
     * @return string
     */
    public function assignedId(): string
    {
        return $this->assignedId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
