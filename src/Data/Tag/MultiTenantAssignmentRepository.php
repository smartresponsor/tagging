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
final class MultiTenantAssignmentRepository implements TagAssignmentRepositoryInterface
{
    private FileTagAssignmentRepository $inner;

    /**
     * @param string $tenantId
     * @param string $baseDir
     */
    public function __construct(string $tenantId, string $baseDir = 'report/tag')
    {
        $path = rtrim($baseDir, '/') . '/' . rawurlencode($tenantId) . '/assignment.ndjson';
        $this->inner = new FileTagAssignmentRepository($path);
    }

    /**
     * @param string $tagId
     * @param string $entityType
     * @param string $entityId
     * @return bool
     */
    public function assign(string $tagId, string $entityType, string $entityId): bool
    {
        return $this->inner->assign($tagId, $entityType, $entityId);
    }

    /**
     * @param string $tagId
     * @param string $entityType
     * @param string $entityId
     * @return bool
     */
    public function unassign(string $tagId, string $entityType, string $entityId): bool
    {
        return $this->inner->unassign($tagId, $entityType, $entityId);
    }

    /**
     * @param string $entityType
     * @param string $entityId
     * @param int $limit
     * @param int $offset
     * @return array|\App\Data\Tag\AssignmentRecord[]
     */
    public function listByEntity(string $entityType, string $entityId, int $limit = 50, int $offset = 0): array
    {
        return $this->inner->listByEntity($entityType, $entityId, $limit, $offset);
    }

    /**
     * @param string $tagId
     * @return array
     */
    public function unassignAllForTag(string $tagId): array
    {
        return $this->inner->unassignAllForTag($tagId);
    }
}
