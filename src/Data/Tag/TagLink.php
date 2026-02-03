<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Data\Tag;

final class TagLink
{
    public function __construct(
        private string $tenant,
        private string $entityType,   // category|product|project|text
        private string $entityId,
        private string $tagId,        // ULID (26)
        private ?\DateTimeImmutable $createdAt = null,
    ) {}

    public function tenant(): string { return $this->tenant; }
    public function entityType(): string { return $this->entityType; }
    public function entityId(): string { return $this->entityId; }
    public function tagId(): string { return $this->tagId; }
    public function createdAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
