<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag')]
#[ORM\UniqueConstraint(name: 'tag_slug_uq', columns: ['tenant', 'slug'])]
final class Tag
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 26)]
        private readonly string $id,
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Column(type: 'string')]
        private string $slug,
        #[ORM\Column(type: 'string')]
        private string $label,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private readonly \DateTimeImmutable $createdAt,
        #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
        #[ORM\Column(name: 'required_flag', type: 'boolean')]
        private bool $requiredFlag = false,
        #[ORM\Column(name: 'mod_only_flag', type: 'boolean')]
        private bool $modOnlyFlag = false,
    ) {}

    public static function create(string $tenant, string $id, string $slug, string $label): self
    {
        $now = new \DateTimeImmutable();

        return new self($id, $tenant, $slug, $label, $now, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function requiredFlag(): bool
    {
        return $this->requiredFlag;
    }

    public function modOnlyFlag(): bool
    {
        return $this->modOnlyFlag;
    }

    public function rename(string $label): void
    {
        $this->label = $label;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setFlags(bool $requiredFlag, bool $modOnlyFlag): void
    {
        $this->requiredFlag = $requiredFlag;
        $this->modOnlyFlag = $modOnlyFlag;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
