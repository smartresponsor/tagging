<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_classification')]
#[ORM\UniqueConstraint(name: 'tag_classification_uq', columns: ['tenant', 'scope', 'ref_id', 'key'])]
final class TagClassification
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $id,
        #[ORM\Column(type: 'string')]
        private string $scope,
        #[ORM\Column(name: 'ref_id', type: 'string')]
        private string $refId,
        #[ORM\Column(type: 'string')]
        private string $key,
        #[ORM\Column(type: 'string')]
        private string $value,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function scope(): string
    {
        return $this->scope;
    }

    public function refId(): string
    {
        return $this->refId;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
