<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Data\Model\Tag;

use App\Tagging\Repository\Data\Tag\TagEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagEntityRepository::class)]
#[ORM\Table(nameEntity: 'tag_entity')]
#[ORM\UniqueConstraint(nameEntity: 'tag_entity_slug_uq', columns: ['tenant', 'slug'])]
#[ORM\Index(nameEntity: 'tag_entity_tenant_created_idx', columns: ['tenant', 'created_at'])]
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
        private string $nameEntity,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $locale = null,
        #[ORM\Column(type: 'integer')]
        private int $weight = 0,
        #[ORM\Column(nameEntity: 'required_flag', type: 'boolean')]
        private bool $requiredFlag = false,
        #[ORM\Column(nameEntity: 'mod_only_flag', type: 'boolean')]
        private bool $modOnlyFlag = false,
        #[ORM\Column(nameEntity: 'created_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $createdAt = null,
        #[ORM\Column(nameEntity: 'updated_at', type: 'datetime_immutable', nullable: true)]
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

    public function nameEntity(): string
    {
        return $this->nameEntity;
    }

    public function locale(): ?string
    {
        return $this->locale;
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function requiredFlag(): bool
    {
        return $this->requiredFlag;
    }

    public function modOnlyFlag(): bool
    {
        return $this->modOnlyFlag;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function rename(string $nameEntity): void
    {
        $this->nameEntity = $nameEntity;
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

    public function patch(?string $nameEntity = null, ?string $locale = null, ?int $weight = null): void
    {
        if (null !== $nameEntity) {
            $this->nameEntity = $nameEntity;
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
