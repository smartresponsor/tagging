<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Event\Lifecycle\Tag;

final readonly class TagAssigned
{
    public function __construct(
        public string $tenant,
        public string $tagId,
        public string $entityType,
        public string $entityId,
        public \DateTimeImmutable $at,
    ) {}
}
