<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_redirect')]
final class TagRedirect
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Id]
        #[ORM\Column(name: 'from_slug', type: 'string')]
        private string $fromSlug,
        #[ORM\Column(name: 'to_tag_id', type: 'string', length: 26)]
        private string $toTagId,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function fromSlug(): string
    {
        return $this->fromSlug;
    }

    public function toTagId(): string
    {
        return $this->toTagId;
    }
}
