<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Data\Model\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_entity')]
#[ORM\UniqueConstraint(name: 'tag_entity_slug_uq', columns: ['tenant', 'slug'])]
final class TagEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 26)]
        private string $id,        // ULID (26)
        #[ORM\Column(type: 'string')]
        private string $slug,
        #[ORM\Column(type: 'string')]
        private string $name,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $locale = null,
        #[ORM\Column(type: 'integer')]
        private int $weight = 0,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $createdAt = null,
        #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt ??= $now;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function locale(): ?string
    {
        return $this->locale;
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function patch(?string $name = null, ?string $locale = null, ?int $weight = null): void
    {
        if (null !== $name) {
            $this->name = $name;
        }
        if (null !== $locale) {
            $this->locale = $locale;
        }
        if (null !== $weight) {
            $this->weight = $weight;
        }
        $this->updatedAt = new \DateTimeImmutable();
    }
}
