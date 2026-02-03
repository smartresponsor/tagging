<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Data\Tag;

final class MultiTenantAssignmentRepository implements TagAssignmentRepositoryInterface
{
    private FileTagAssignmentRepository $inner;
    public function __construct(string $tenantId, string $baseDir='report/tag')
    {
        $path = rtrim($baseDir, '/').'/'.rawurlencode($tenantId).'/assignment.ndjson';
        $this->inner = new FileTagAssignmentRepository($path);
    }
    public function assign(string $tagId, string $entityType, string $entityId): bool { return $this->inner->assign($tagId,$entityType,$entityId); }
    public function unassign(string $tagId, string $entityType, string $entityId): bool { return $this->inner->unassign($tagId,$entityType,$entityId); }
    public function listByEntity(string $entityType, string $entityId, int $limit=50, int $offset=0): array { return $this->inner->listByEntity($entityType,$entityId,$limit,$offset); }
    public function unassignAllForTag(string $tagId): array { return $this->inner->unassignAllForTag($tagId); }
}
