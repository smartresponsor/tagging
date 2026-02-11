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
final readonly class TagScheme
{
    /**
     * @param string $id
     * @param string $name
     * @param string|null $locale
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        private string            $id,
        private string            $name,
        private ?string           $locale,
        private DateTimeImmutable $createdAt
    )
    {
    }

    /**
     * @param string $id
     * @param string $name
     * @param string|null $locale
     * @return self
     */
    public static function create(string $id, string $name, ?string $locale): self
    {
        return new self($id, $name, $locale, new DateTimeImmutable());
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
     * @return \DateTimeImmutable
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
