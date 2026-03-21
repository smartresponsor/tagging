<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagReadResponder;
use App\Service\Core\Tag\TagReadModelInterface;

final readonly class AssignmentReadController
{
    public function __construct(private TagReadModelInterface $read, private TagReadResponder $responder = new TagReadResponder())
    {
    }

    /**
     * GET /tag/assignments?entityType=...&entityId=...&limit=...
     *
     * @return array{0:int,1:array<string,string>,2:string}
     */
    public function listByEntity(array $req): array
    {
        $tenant = TagHttpRequest::tenant($req);
        if ('' === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $q = TagHttpRequest::query($req);
        $etype = trim((string) ($q['entityType'] ?? ($q['entity_type'] ?? '')));
        $eid = trim((string) ($q['entityId'] ?? ($q['entity_id'] ?? '')));
        $limit = max(1, min(500, (int) ($q['limit'] ?? 100)));

        if ('' === $etype || '' === $eid) {
            return $this->responder->bad('validation_failed');
        }

        $items = $this->read->tagsForEntity($tenant, $etype, $eid, $limit);

        return $this->responder->ok([
            'ok' => true,
            'entityType' => $etype,
            'entityId' => $eid,
            'items' => $items,
        ]);
    }
}
