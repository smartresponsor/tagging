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
final class Tag
{
    /**
     * @param string $id
     * @param string $slug
     * @param string $label
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        private readonly string            $id,
        private string                     $slug,
        private string                     $label,
        private readonly DateTimeImmutable $createdAt
    )
    {
    }

    /**
     * @param string $id
     * @param string $slug
     * @param string $label
     * @return self
     */
    public static function create(string $id, string $slug, string $label): self
    {
        return new self($id, $slug, $label, new DateTimeImmutable());
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
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param string $label
     * @return void
     */
    public function rename(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param string $slug
     * @return void
     */
    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
