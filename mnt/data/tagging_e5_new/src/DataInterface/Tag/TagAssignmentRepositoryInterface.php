<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\DataInterface\Tag;

use App\Data\Tag\AssignmentRecord;

interface TagAssignmentRepositoryInterface
{
    /** Idempotent: returns true if created, false if existed */
    public function assign(string $tagId, string $entityType, string $entityId): bool;

    /** Idempotent: returns true if removed, false if not present */
    public function unassign(string $tagId, string $entityType, string $entityId): bool;

    /** @return AssignmentRecord[] */
    public function listByEntity(string $entityType, string $entityId, int $limit = 50, int $offset = 0): array;

    /** @return array{removed:int} */
    public function unassignAllForTag(string $tagId): array;
}
