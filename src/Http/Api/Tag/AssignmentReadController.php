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
        $tenant = $this->tenant($req);
        if (null === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $query = TagHttpRequest::query($req);
        $entityType = $this->queryValue($query, 'entityType', 'entity_type');
        $entityId = $this->queryValue($query, 'entityId', 'entity_id');
        $limit = $this->boundedInt($query['limit'] ?? 100, 100, 1, 500);

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

    private function tenant(array $request): ?string
    {
        $tenant = TagHttpRequest::tenant($request);

        return '' !== $tenant ? $tenant : null;
    }

    private function queryValue(array $query, string $primary, string $fallback): string
    {
        return trim((string) ($query[$primary] ?? ($query[$fallback] ?? '')));
    }

    private function boundedInt(mixed $value, int $default, int $min, int $max): int
    {
        return max($min, min($max, (int) ($value ?? $default)));
    }
}
