<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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
