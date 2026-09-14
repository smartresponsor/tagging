<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Tag;

use App\Tagging\Repository\Core\Tag\TagSchemeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagSchemeRepository::class)]
#[ORM\Table(name: 'tag_scheme')]
#[ORM\UniqueConstraint(name: 'tag_scheme_name_uq', columns: ['tenant', 'nameEntity'])]
final class TagSchemeEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $id,
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Column(type: 'string')]
        private string $nameEntity,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $locale,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private \DateTimeImmutable $createdAt,
    ) {}

    public static function create(string $tenant, string $id, string $nameEntity, ?string $locale): self
    {
        return new self($id, $tenant, $nameEntity, $locale, new \DateTimeImmutable());
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function nameEntity(): string
    {
        return $this->nameEntity;
    }

    public function locale(): ?string
    {
        return $this->locale;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
