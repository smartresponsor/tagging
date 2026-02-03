<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Domain\Tag;

final class TagAssignment {
    public function __construct(
        private string $id,
        private string $tagId,
        private string $assignedType,
        private string $assignedId,
        private \DateTimeImmutable $createdAt
    ) {}

    public static function create(string $id, string $tagId, string $assignedType, string $assignedId): self {
        return new self($id, $tagId, $assignedType, $assignedId, new \DateTimeImmutable());
    }

    public function id(): string { return $this->id; }
    public function tagId(): string { return $this->tagId; }
    public function assignedType(): string { return $this->assignedType; }
    public function assignedId(): string { return $this->assignedId; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
}
