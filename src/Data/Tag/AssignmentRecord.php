<?php
declare(strict_types=1);

namespace App\Data\Tag;

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
class AssignmentRecord
{
    public function __construct(
        public string $tagId,
        public string $entityType,
        public string $entityId,
        public string $createdAt
    ) {
    }
}
