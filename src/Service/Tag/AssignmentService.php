<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\DataInterface\Tag\TagAssignmentRepositoryInterface;

final class AssignmentService
{
    public function __construct(private TagAssignmentRepositoryInterface $repo){}

    public function assign(string $tagId, string $entityType, string $entityId): array
    {
        $created = $this->repo->assign($tagId, $entityType, $entityId);
        return ['assigned' => $created ? 1 : 0];
    }

    public function unassign(string $tagId, string $entityType, string $entityId): array
    {
        $removed = $this->repo->unassign($tagId, $entityType, $entityId);
        return ['removed' => $removed ? 1 : 0];
    }

    public function listByEntity(string $entityType, string $entityId, int $limit=50, int $offset=0): array
    {
        $rows = $this->repo->listByEntity($entityType, $entityId, $limit, $offset);
        return ['items' => array_map(fn($r)=>['tagId'=>$r->tagId,'createdAt'=>$r->createdAt], $rows)];
    }

    public function assignBulk(string $entityType, string $entityId, array $tagIds): array
    {
        $ok=0; foreach ($tagIds as $t) { if ($this->repo->assign((string)$t, $entityType, $entityId)) $ok++; }
        return ['assigned' => $ok];
    }
}
