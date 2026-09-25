<?php

declare(strict_types=1);

namespace App\Tagging\Entity\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_assignment_effect')]
#[ORM\Index(name: 'tag_assignment_effect_source_idx', columns: ['tenant', 'source_scope', 'source_id'])]
#[ORM\Index(name: 'tag_assignment_effect_assigned_idx', columns: ['tenant', 'assigned_type', 'assigned_id'])]
#[ORM\UniqueConstraint(name: 'tag_assignment_effect_uq', columns: ['tenant', 'id'])]
final class TagAssignmentEffectEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private readonly string $id,
        #[ORM\Column(type: 'string')]
        private readonly string $tenant,
        #[ORM\Column(name: 'assigned_type', type: 'string')]
        private readonly string $assignedType,
        #[ORM\Column(name: 'assigned_id', type: 'string')]
        private readonly string $assignedId,
        #[ORM\Column(type: 'string')]
        private readonly string $key,
        #[ORM\Column(type: 'string')]
        private readonly string $value,
        #[ORM\Column(name: 'source_scope', type: 'string')]
        private readonly string $sourceScope,
        #[ORM\Column(name: 'source_id', type: 'string')]
        private readonly string $sourceId,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if ('' === trim($tenant)) {
            throw new \InvalidArgumentException('Projection tenant must not be empty.');
        }

        if ('' === trim($assignedType) || '' === trim($assignedId)) {
            throw new \InvalidArgumentException('Projection assignment target must not be empty.');
        }

        if ('' === trim($key)) {
            throw new \InvalidArgumentException('Projection key must not be empty.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function assignedType(): string
    {
        return $this->assignedType;
    }

    public function assignedId(): string
    {
        return $this->assignedId;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function sourceScope(): string
    {
        return $this->sourceScope;
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
