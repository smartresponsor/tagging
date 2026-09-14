<?php

declare(strict_types=1);

namespace App\Tagging\Entity\Tag;

use App\Tagging\Repository\Core\Tag\TagAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagAssignmentRepository::class)]
#[ORM\Table(name: 'tag_assignment')]
#[ORM\UniqueConstraint(
    name: 'tag_assignment_uq',
    columns: ['tenant', 'tag_id', 'assigned_type', 'assigned_id'],
)]
#[ORM\Index(name: 'tag_assignment_subject_idx', columns: ['tenant', 'assigned_type', 'assigned_id'])]
#[ORM\Index(name: 'tag_assignment_tag_idx', columns: ['tenant', 'tag_id'])]
final class TagAssignmentEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private readonly string $id,
        #[ORM\Column(type: 'string')]
        private readonly string $tenant,
        #[ORM\Column(name: 'tag_id', type: 'string', length: 26)]
        private readonly string $tagId,
        #[ORM\Column(name: 'assigned_type', type: 'string')]
        private readonly string $assignedType,
        #[ORM\Column(name: 'assigned_id', type: 'string')]
        private readonly string $assignedId,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if ('' === trim($tenant)) {
            throw new \InvalidArgumentException('Assignment tenant must not be empty.');
        }

        if ('' === trim($tagId)) {
            throw new \InvalidArgumentException('Assignment tag id must not be empty.');
        }

        if ('' === trim($assignedType) || '' === trim($assignedId)) {
            throw new \InvalidArgumentException('Assigned type and identifier must not be empty.');
        }
    }

    public static function create(
        string $tenant,
        string $id,
        string $tagId,
        string $assignedType,
        string $assignedId,
    ): self {
        return new self(
            id: trim($id),
            tenant: trim($tenant),
            tagId: trim($tagId),
            assignedType: trim($assignedType),
            assignedId: trim($assignedId),
        );
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
