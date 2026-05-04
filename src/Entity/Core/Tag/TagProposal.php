<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_proposal')]
final class TagProposal
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $id,
        #[ORM\Column(type: 'string')]
        private string $type,
        #[ORM\Column(type: 'json')]
        private array $payload,
        #[ORM\Column(type: 'string')]
        private string $status = 'pending',
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $createdAt = null,
        #[ORM\Column(name: 'decided_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $decidedAt = null,
        #[ORM\Column(name: 'decided_by', type: 'string', nullable: true)]
        private ?string $decidedBy = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function setStatus(string $status, ?string $decidedBy): void
    {
        $this->status = $status;
        $this->decidedAt = new \DateTimeImmutable();
        $this->decidedBy = $decidedBy;
    }
}
