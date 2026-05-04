<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_assignment')]
final class TagAssignment
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
        #[ORM\Column(name: 'assigned_type', type: 'string')]
        private string $assignedType,
        #[ORM\Column(name: 'assigned_id', type: 'string')]
        private string $assignedId,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private \DateTimeImmutable $createdAt,
    ) {}

    public static function create(string $tenant, string $id, string $tagId, string $assignedType, string $assignedId): self
    {
        return new self($id, $tenant, $tagId, $assignedType, $assignedId, new \DateTimeImmutable());
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

    public function assignedType(): string
    {
        return $this->assignedType;
    }

    public function assignedId(): string
    {
        return $this->assignedId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
