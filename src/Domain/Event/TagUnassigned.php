<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Domain\Event;

final class TagUnassigned
{
    public function __construct(
        public readonly string $tenant,
        public readonly string $tagId,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly \DateTimeImmutable $at
    ) {}
}
