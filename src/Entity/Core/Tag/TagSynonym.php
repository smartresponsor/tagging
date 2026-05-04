<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_synonym')]
#[ORM\UniqueConstraint(name: 'tag_synonym_uq', columns: ['tenant', 'tag_id', 'label'])]
final class TagSynonym
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $id,
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Column(name: 'tag_id', type: 'string', length: 26)]
        private string $tagId,
        #[ORM\Column(type: 'string')]
        private string $label,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public static function create(string $tenant, string $id, string $tagId, string $label): self
    {
        return new self($id, $tenant, $tagId, $label);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function tagId(): string
    {
        return $this->tagId;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt ?? new \DateTimeImmutable();
    }
}
