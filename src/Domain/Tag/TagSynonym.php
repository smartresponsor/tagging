<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Domain\Tag;

/**
 *
 */

/**
 *
 */
final class TagSynonym
{
    /**
     * @param string $id
     * @param string $tagId
     * @param string $label
     */
    public function __construct(
        private readonly string $id,
        private readonly string $tagId,
        private readonly string $label
    )
    {
    }

    /**
     * @param string $id
     * @param string $tagId
     * @param string $label
     * @return self
     */
    public static function create(string $id, string $tagId, string $label): self
    {
        return new self($id, $tagId, $label);
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
    public function label(): string
    {
        return $this->label;
    }
}
