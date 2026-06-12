<?php

declare(strict_types=1);

namespace App\Tagging\Entity\Tag;

use App\Tagging\Repository\Core\Tag\TagSynonymRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagSynonymRepository::class)]
#[ORM\Table(name: 'tag_synonym')]
#[ORM\UniqueConstraint(name: 'tag_synonym_uq', columns: ['tenant', 'tag_id', 'label'])]
#[ORM\Index(name: 'tag_synonym_tag_idx', columns: ['tenant', 'tag_id'])]
final class TagSynonymEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private readonly string $id,
        #[ORM\Column(type: 'string')]
        private readonly string $tenant,
        #[ORM\ManyToOne(targetEntity: TagEntity::class)]
        #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private readonly TagEntity $tag,
        #[ORM\Column(type: 'string')]
        private readonly string $label,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if ($tenant !== $tag->tenant()) {
            throw new \InvalidArgumentException('A tag synonym cannot cross tenants.');
        }

        if ('' === trim($label)) {
            throw new \InvalidArgumentException('A tag synonym label must not be empty.');
        }
    }

    public static function create(string $id, TagEntity $tag, string $label): self
    {
        return new self(
            id: $id,
            tenant: $tag->tenant(),
            tag: $tag,
            label: trim($label),
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

    public function tag(): TagEntity
    {
        return $this->tag;
    }

    public function tagId(): string
    {
        return $this->tag->id();
    }

    public function label(): string
    {
        return $this->label;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
