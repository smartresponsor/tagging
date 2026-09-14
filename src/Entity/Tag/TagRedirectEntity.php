<?php

declare(strict_types=1);

namespace App\Tagging\Entity\Tag;

use App\Tagging\Repository\Core\Tag\TagRedirectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRedirectRepository::class)]
#[ORM\Table(name: 'tag_redirect')]
#[ORM\UniqueConstraint(name: 'tag_redirect_uq', columns: ['tenant', 'from_slug'])]
#[ORM\Index(name: 'tag_redirect_to_tag_idx', columns: ['tenant', 'to_tag_id'])]
final class TagRedirectEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private readonly string $id,
        #[ORM\Column(type: 'string')]
        private readonly string $tenant,
        #[ORM\Column(name: 'from_slug', type: 'string')]
        private readonly string $fromSlug,
        #[ORM\ManyToOne(targetEntity: TagEntity::class)]
        #[ORM\JoinColumn(name: 'to_tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private readonly TagEntity $toTag,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if ($tenant !== $toTag->tenant()) {
            throw new \InvalidArgumentException('A tag redirect cannot cross tenants.');
        }

        if ('' === trim($fromSlug)) {
            throw new \InvalidArgumentException('A tag redirect source slug must not be empty.');
        }

        if ($fromSlug === $toTag->slug()) {
            throw new \InvalidArgumentException('A tag redirect cannot target the same slug.');
        }
    }

    public static function create(string $id, string $fromSlug, TagEntity $toTag): self
    {
        return new self(
            id: $id,
            tenant: $toTag->tenant(),
            fromSlug: trim($fromSlug),
            toTag: $toTag,
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

    public function fromSlug(): string
    {
        return $this->fromSlug;
    }

    public function toTag(): TagEntity
    {
        return $this->toTag;
    }

    public function toTagId(): string
    {
        return $this->toTag->id();
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
