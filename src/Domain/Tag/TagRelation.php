<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Domain\Tag;

use InvalidArgumentException;

/**
 *
 */

/**
 *
 */
final readonly class TagRelation
{
    /**
     * @param string $id
     * @param string $fromTagId
     * @param string $toTagId
     * @param string $type
     */
    public function __construct(
        private string $id,
        private string $fromTagId,
        private string $toTagId,
        private string $type // 'broader' | 'related'
    )
    {
    }

    /**
     * @param string $id
     * @param string $fromTagId
     * @param string $toTagId
     * @param string $type
     * @return self
     */
    public static function create(string $id, string $fromTagId, string $toTagId, string $type): self
    {
        if (!in_array($type, ['broader', 'related'], true)) throw new InvalidArgumentException('invalid relation type');
        return new self($id, $fromTagId, $toTagId, $type);
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
    public function fromTagId(): string
    {
        return $this->fromTagId;
    }

    /**
     * @return string
     */
    public function toTagId(): string
    {
        return $this->toTagId;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }
}
