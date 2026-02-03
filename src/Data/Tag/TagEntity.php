<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Data\Tag;

final class TagEntity
{
    public function __construct(
        private string $tenant,
        private string $id,        // ULID (26)
        private string $slug,
        private string $name,
        private ?string $locale = null,
        private int $weight = 0,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function tenant(): string { return $this->tenant; }
    public function id(): string { return $this->id; }
    public function slug(): string { return $this->slug; }
    public function name(): string { return $this->name; }
    public function locale(): ?string { return $this->locale; }
    public function weight(): int { return $this->weight; }
    public function createdAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
