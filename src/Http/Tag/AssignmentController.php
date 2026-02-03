<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Http\Tag;

use App\Service\Tag\AssignmentService;
use App\Data\Tag\FileTagAssignmentRepository;

final class AssignmentController
{
    private AssignmentService $svc;

    public function __construct(?AssignmentService $svc=null, ?string $filePath=null)
    {
        if ($svc) { $this->svc = $svc; return; }
        $repo = new FileTagAssignmentRepository($filePath ?: 'report/tag/assignment.ndjson');
        $this->svc = new AssignmentService($repo);
    }

    public function assign(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $entityType = (string)($body['entityType'] ?? '');
        $entityId = (string)($body['entityId'] ?? '');
        if ($tagId === '' || $entityType === '' || $entityId === '') {
            return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>'bad_request'])];
        }
        $res = $this->svc->assign($tagId, $entityType, $entityId);
        return [200, ['Content-Type'=>'application/json'], json_encode($res)];
    }

    public function unassign(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $entityType = (string)($body['entityType'] ?? '');
        $entityId = (string)($body['entityId'] ?? '');
        if ($tagId === '' || $entityType === '' || $entityId === '') {
            return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>'bad_request'])];
        }
        $res = $this->svc->unassign($tagId, $entityType, $entityId);
        return [200, ['Content-Type'=>'application/json'], json_encode($res)];
    }

    public function listByEntity(array $query): array
    {
        $entityType = (string)($query['entityType'] ?? '');
        $entityId   = (string)($query['entityId'] ?? '');
        $limit  = (int)($query['limit'] ?? 50);
        $offset = (int)($query['offset'] ?? 0);
        if ($entityType === '' || $entityId === '') {
            return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>'bad_request'])];
        }
        $res = $this->svc->listByEntity($entityType, $entityId, $limit, $offset);
        return [200, ['Content-Type'=>'application/json'], json_encode($res)];
    }

    public function assignBulk(array $body): array
    {
        $entityType = (string)($body['entityType'] ?? '');
        $entityId   = (string)($body['entityId'] ?? '');
        $tagIds     = (array)($body['tagIds'] ?? []);
        if ($entityType === '' || $entityId === '' || $tagIds === []) {
            return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>'bad_request'])];
        }
        $svc = $this->svc;
        $ok=0; foreach ($tagIds as $t){ $r = $svc->assign((string)$t, $entityType, $entityId); $ok += (int)($r['assigned'] ?? 0); }
        return [200, ['Content-Type'=>'application/json'], json_encode(['assigned'=>$ok])];
    }
}
