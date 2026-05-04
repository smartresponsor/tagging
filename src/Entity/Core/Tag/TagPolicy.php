<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tag_policy')]
final class TagPolicy
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Column(type: 'json')]
        private array $policy = [],
        #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->updatedAt ??= new \DateTimeImmutable();
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    /** @return array<string,mixed> */
    public function policy(): array
    {
        return $this->policy;
    }

    /** @param array<string,mixed> $policy */
    public function setPolicy(array $policy): void
    {
        $this->policy = $policy;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
