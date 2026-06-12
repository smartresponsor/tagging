<?php

declare(strict_types=1);

namespace App\Tagging\Entity\Projection\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'tag_admin_view')]
#[ORM\UniqueConstraint(name: 'tag_admin_view_tag_uq', columns: ['tenant', 'tag_id'])]
#[ORM\UniqueConstraint(name: 'tag_admin_view_slug_uq', columns: ['tenant', 'slug'])]
final class TagAdminViewProjection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'tag_id', type: 'string', length: 26)]
    private string $tagId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $tenant;

    #[ORM\Column(type: 'string', length: 255)]
    private string $slug;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'required_flag', type: 'boolean')]
    private bool $requiredFlag = false;

    #[ORM\Column(name: 'mod_only_flag', type: 'boolean')]
    private bool $modOnlyFlag = false;

    public function id(): ?int
    {
        return $this->id;
    }

    public function tagId(): string
    {
        return $this->tagId;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function requiredFlag(): bool
    {
        return $this->requiredFlag;
    }

    public function modOnlyFlag(): bool
    {
        return $this->modOnlyFlag;
    }
}
