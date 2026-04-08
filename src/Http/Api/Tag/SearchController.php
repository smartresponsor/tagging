<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagReadResponder;
use App\Service\Core\Tag\SearchService;

final readonly class SearchController
{
    public function __construct(
        private SearchService $svc,
        private TagReadResponder $responder = new TagReadResponder(),
    ) {
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req): array
    {
        $tenant = TagHttpRequest::tenantOrNull($req);
        if (null === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $result = $this->svc->search(
            $tenant,
            TagHttpRequest::queryString($req, 'q'),
            TagHttpRequest::queryInt($req, 'pageSize', 20, 1, 100),
            $this->optionalString(TagHttpRequest::queryString($req, 'pageToken')),
        );

        return $this->responder->ok([
            'ok' => true,
            'items' => $result['items'] ?? [],
            'total' => $result['total'] ?? -1,
            'nextPageToken' => $result['nextPageToken'] ?? null,
            'cacheHit' => (bool) ($result['cacheHit'] ?? false),
        ]);
    }

    private function optionalString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return '' !== $value ? $value : null;
    }
}
