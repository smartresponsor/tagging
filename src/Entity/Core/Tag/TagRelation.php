<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_relation')]
#[ORM\UniqueConstraint(name: 'tag_relation_uq', columns: ['tenant', 'from_tag_id', 'to_tag_id', 'type'])]
final class TagRelation
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $id,
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Column(name: 'from_tag_id', type: 'string', length: 26)]
        private string $fromTagId,
        #[ORM\Column(name: 'to_tag_id', type: 'string', length: 26)]
        private string $toTagId,
        #[ORM\Column(type: 'string')]
        private string $type, // 'broader' | 'related'
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public static function create(string $tenant, string $id, string $fromTagId, string $toTagId, string $type): self
    {
        if (!in_array($type, ['broader', 'related'], true)) {
            throw new \InvalidArgumentException('invalid relation type');
        }

        return new self($id, $tenant, $fromTagId, $toTagId, $type);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function fromTagId(): string
    {
        return $this->fromTagId;
    }

    public function toTagId(): string
    {
        return $this->toTagId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt ?? new \DateTimeImmutable();
    }
}
