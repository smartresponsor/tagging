<?php

declare(strict_types=1);

namespace App\Tagging\Entity\Tag;

use App\Tagging\Repository\Core\Tag\TagRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRelationRepository::class)]
#[ORM\Table(name: 'tag_relation')]
#[ORM\UniqueConstraint(name: 'tag_relation_uq', columns: ['tenant', 'from_tag_id', 'to_tag_id', 'type'])]
#[ORM\Index(name: 'tag_relation_from_tag_idx', columns: ['tenant', 'from_tag_id'])]
#[ORM\Index(name: 'tag_relation_to_tag_idx', columns: ['tenant', 'to_tag_id'])]
final class TagRelationEntity
{
    private const TYPE = [
        'broader',
        'narrower',
        'related',
        'equivalent',
        'parent',
        'child',
    ];

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private readonly string $id,
        #[ORM\Column(type: 'string')]
        private readonly string $tenant,
        #[ORM\ManyToOne(targetEntity: TagEntity::class)]
        #[ORM\JoinColumn(name: 'from_tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private readonly TagEntity $fromTag,
        #[ORM\ManyToOne(targetEntity: TagEntity::class)]
        #[ORM\JoinColumn(name: 'to_tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private readonly TagEntity $toTag,
        #[ORM\Column(type: 'string')]
        private readonly string $type,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if ($fromTag->tenant() !== $toTag->tenant() || $tenant !== $fromTag->tenant()) {
            throw new \InvalidArgumentException('A tag relation cannot cross tenants.');
        }

        if ($fromTag->id() === $toTag->id()) {
            throw new \InvalidArgumentException('A tag relation cannot point to the same tag.');
        }

        if (!in_array($type, self::TYPE, true)) {
            throw new \InvalidArgumentException('Invalid tag relation type.');
        }
    }

    public static function create(string $id, TagEntity $fromTag, TagEntity $toTag, string $type): self
    {
        return new self(
            id: $id,
            tenant: $fromTag->tenant(),
            fromTag: $fromTag,
            toTag: $toTag,
            type: $type,
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

    public function fromTag(): TagEntity
    {
        return $this->fromTag;
    }

    public function fromTagId(): string
    {
        return $this->fromTag->id();
    }

    public function toTag(): TagEntity
    {
        return $this->toTag;
    }

    public function toTagId(): string
    {
        return $this->toTag->id();
    }

    public function type(): string
    {
        return $this->type;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
