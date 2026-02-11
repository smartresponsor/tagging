<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag;

use App\Data\Tag\FileTagAssignmentRepository;
use App\Service\Tag\AssignmentService;

/**
 *
 */

/**
 *
 */
final class AssignmentController
{
    private AssignmentService $svc;

    /**
     * @param \App\Service\Tag\AssignmentService|null $svc
     * @param string|null $filePath
     */
    public function __construct(?AssignmentService $svc = null, ?string $filePath = null)
    {
        if ($svc) {
            $this->svc = $svc;
            return;
        }
        $repo = new FileTagAssignmentRepository($filePath ?: 'report/tag/assignment.ndjson');
        $this->svc = new AssignmentService($repo);
    }

    /**
     * @param array $params
     * @param array $body
     * @return array
     */
    public function assign(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $entityType = (string)($body['entityType'] ?? '');
        $entityId = (string)($body['entityId'] ?? '');
        if ($tagId === '' || $entityType === '' || $entityId === '') {
            return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        }
        $res = $this->svc->assign($tagId, $entityType, $entityId);
        return [200, ['Content-Type' => 'application/json'], json_encode($res)];
    }

    /**
     * @param array $params
     * @param array $body
     * @return array
     */
    public function unassign(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $entityType = (string)($body['entityType'] ?? '');
        $entityId = (string)($body['entityId'] ?? '');
        if ($tagId === '' || $entityType === '' || $entityId === '') {
            return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        }
        $res = $this->svc->unassign($tagId, $entityType, $entityId);
        return [200, ['Content-Type' => 'application/json'], json_encode($res)];
    }

    /**
     * @param array $query
     * @return array
     */
    public function listByEntity(array $query): array
    {
        $entityType = (string)($query['entityType'] ?? '');
        $entityId = (string)($query['entityId'] ?? '');
        $limit = (int)($query['limit'] ?? 50);
        $offset = (int)($query['offset'] ?? 0);
        if ($entityType === '' || $entityId === '') {
            return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        }
        $res = $this->svc->listByEntity($entityType, $entityId, (string)$limit, $offset);
        return [200, ['Content-Type' => 'application/json'], json_encode($res)];
    }

    /**
     * @param array $body
     * @return array
     */
    public function assignBulk(array $body): array
    {
        $entityType = (string)($body['entityType'] ?? '');
        $entityId = (string)($body['entityId'] ?? '');
        $tagIds = (array)($body['tagIds'] ?? []);
        if ($entityType === '' || $entityId === '' || $tagIds === []) {
            return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        }
        $svc = $this->svc;
        $ok = 0;
        foreach ($tagIds as $t) {
            $r = $svc->assign((string)$t, $entityType, $entityId);
            $ok += $r['assigned'] ?? 0;
        }
        return [200, ['Content-Type' => 'application/json'], json_encode(['assigned' => $ok])];
    }
}
