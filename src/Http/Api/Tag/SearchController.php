<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagReadResponder;
use App\Service\Core\Tag\SearchService;

final readonly class SearchController
{
    public function __construct(private SearchService $svc, private TagReadResponder $responder = new TagReadResponder()) {}

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req): array
    {
        $tenant = $this->tenant($req);
        if (null === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $query = TagHttpRequest::query($req);
        $result = $this->svc->search(
            $tenant,
            (string) ($query['q'] ?? ''),
            $this->boundedInt($query['pageSize'] ?? 20, 20, 1, 100),
            $this->optionalString($query['pageToken'] ?? null),
        );

        return $this->responder->ok([
            'ok' => true,
            'items' => $result['items'] ?? [],
            'total' => $result['total'] ?? -1,
            'nextPageToken' => $result['nextPageToken'] ?? null,
            'cacheHit' => (bool) ($result['cacheHit'] ?? false),
            'result' => $result,
        ]);
    }

    private function tenant(array $request): ?string
    {
        $tenant = TagHttpRequest::tenant($request);

        return '' !== $tenant ? $tenant : null;
    }

    private function optionalString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return '' !== $value ? $value : null;
    }

    private function boundedInt(mixed $value, int $default, int $min, int $max): int
    {
        return max($min, min($max, (int) ($value ?? $default)));
    }
}
