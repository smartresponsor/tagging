<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Entity\Core\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'idempotency_store')]
final class TagIdempotencyStore
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        private string $tenant,
        #[ORM\Id]
        #[ORM\Column(name: 'key', type: 'string')]
        private string $key,
        #[ORM\Column(type: 'string')]
        private string $op,
        #[ORM\Column(type: 'string')]
        private string $checksum,
        #[ORM\Column(type: 'string')]
        private string $status,
        #[ORM\Column(name: 'result_json', type: 'json', nullable: true)]
        private ?array $resultJson = null,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $createdAt = null,
        #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt ??= $now;
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function op(): string
    {
        return $this->op;
    }

    public function checksum(): string
    {
        return $this->checksum;
    }

    public function status(): string
    {
        return $this->status;
    }

    /** @return array<string,mixed>|null */
    public function resultJson(): ?array
    {
        return $this->resultJson;
    }

    public function setPending(string $op, string $checksum): void
    {
        $this->op = $op;
        $this->checksum = $checksum;
        $this->status = 'pending';
        $this->updatedAt = new \DateTimeImmutable();
    }

    /** @param array<string,mixed> $result */
    public function complete(array $result): void
    {
        $this->status = 'done';
        $this->resultJson = $result;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
