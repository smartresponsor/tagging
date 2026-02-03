<?php
declare(strict_types=1);
namespace App\Domain\Tag;

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
final class Tag {
    public function __construct(
        private string $id,
        private string $slug,
        private string $label,
        private \DateTimeImmutable $createdAt
    ) {}

    public static function create(string $id, string $slug, string $label): self {
        return new self($id, $slug, $label, new \DateTimeImmutable());
    }

    public function id(): string { return $this->id; }
    public function slug(): string { return $this->slug; }
    public function label(): string { return $this->label; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }

    public function rename(string $label): void { $this->label = $label; }
    public function changeSlug(string $slug): void { $this->slug = $slug; }
}
