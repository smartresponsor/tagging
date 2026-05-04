<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_assignment_effect')]
final class TagAssignmentEffect
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $id,
        #[ORM\Column(name: 'assigned_type', type: 'string')]
        private string $assignedType,
        #[ORM\Column(name: 'assigned_id', type: 'string')]
        private string $assignedId,
        #[ORM\Column(type: 'string')]
        private string $key,
        #[ORM\Column(type: 'string')]
        private string $value,
        #[ORM\Column(name: 'source_scope', type: 'string')]
        private string $sourceScope,
        #[ORM\Column(name: 'source_id', type: 'string')]
        private string $sourceId,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }
}
