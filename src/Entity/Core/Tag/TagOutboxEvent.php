<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'outbox_event')]
final class TagOutboxEvent
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer')]
        private ?int $id = null,
        #[ORM\Column(type: 'string')]
        private string $tenant = '',
        #[ORM\Column(type: 'string')]
        private string $topic = '',
        #[ORM\Column(type: 'json')]
        private array $payload = [],
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $createdAt = null,
        #[ORM\Column(name: 'delivered_at', type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $deliveredAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    /** @return array<string,mixed> */
    public function payload(): array
    {
        return $this->payload;
    }
}
