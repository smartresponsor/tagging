<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagReadResponder;
use App\Service\Core\Tag\TagReadModelInterface;

final readonly class AssignmentReadController
{
    public function __construct(private TagReadModelInterface $read, private TagReadResponder $responder = new TagReadResponder()) {}

    /**
     * GET /tag/assignments?entityType=...&entityId=...&limit=...
     *
     * @return array{0:int,1:array<string,string>,2:string}
     */
    public function listByEntity(array $req): array
    {
        $tenant = TagHttpRequest::tenantOrNull($req);
        if (null === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $entityType = TagHttpRequest::queryString($req, 'entityType', 'entity_type');
        $entityId = TagHttpRequest::queryString($req, 'entityId', 'entity_id');
        $limit = TagHttpRequest::queryInt($req, 'limit', 100, 1, 500);

        if ('' === $entityType || '' === $entityId) {
            return $this->responder->bad('validation_failed');
        }

        $items = $this->read->tagsForEntity($tenant, $entityType, $entityId, $limit);

        return $this->responder->ok([
            'ok' => true,
            'entityType' => $entityType,
            'entityId' => $entityId,
            'items' => $items,
        ]);
    }
}
