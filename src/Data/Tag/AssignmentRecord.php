<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Data\Tag;

/**
 *
 */

/**
 *
 */
class AssignmentRecord
{
    /**
     * @param string $tagId
     * @param string $entityType
     * @param string $entityId
     * @param string $createdAt
     */
    public function __construct(
        public string $tagId,
        public string $entityType,
        public string $entityId,
        public string $createdAt
    )
    {
    }
}
