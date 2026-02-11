<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Domain\Event;

use DateTimeImmutable;

/**
 *
 */

/**
 *
 */
final readonly class TagUnassigned
{
    /**
     * @param string $tenant
     * @param string $tagId
     * @param string $entityType
     * @param string $entityId
     * @param \DateTimeImmutable $at
     */
    public function __construct(
        public string            $tenant,
        public string            $tagId,
        public string            $entityType,
        public string            $entityId,
        public DateTimeImmutable $at
    )
    {
    }
}
