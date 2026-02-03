<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Tag;

use App\Infra\Tag\TagReadModel;

final class AssignmentReadController
{
    public function __construct(private TagReadModel $read) {}

    /**
     * GET /tag/assignments?entityType=...&entityId=...&limit=...
     *
     * @return array{0:int,1:array<string,string>,2:string}
     */
    public function listByEntity(array $req): array
    {
        $tenant = (string)($req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') return self::bad('invalid_tenant');

        $q = is_array($req['query'] ?? null) ? $req['query'] : [];
        $etype = (string)($q['entityType'] ?? ($q['entity_type'] ?? ''));
        $eid   = (string)($q['entityId']   ?? ($q['entity_id'] ?? ''));
        $limit = (int)($q['limit'] ?? 100);
        $limit = max(1, min(500, $limit));

        if ($etype === '' || $eid === '') return self::bad('validation_failed');

        $items = $this->read->tagsForEntity($tenant, $etype, $eid, $limit);
        return self::ok(['ok'=>true, 'items'=>$items]);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private static function ok(array $body): array
    { return [200, ['Content-Type'=>'application/json'], json_encode($body)]; }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private static function bad(string $code): array
    { return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>$code])]; }
}
