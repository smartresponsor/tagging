<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

use App\Http\Api\Tag\Responder\TagReadResponder;
use App\Service\Core\Tag\SuggestService;

final readonly class SuggestController
{
    public function __construct(private SuggestService $svc, private TagReadResponder $responder = new TagReadResponder()) {}

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req): array
    {
        $tenant = TagHttpRequest::tenant($req);
        if ('' === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $query = TagHttpRequest::query($req);
        $q = (string) ($query['q'] ?? '');
        $limit = max(1, min(50, (int) ($query['limit'] ?? 10)));

        $result = $this->svc->suggest($tenant, $q, $limit);

        return $this->responder->ok([
            'ok' => true,
            'items' => $result['items'] ?? [],
            'cacheHit' => (bool) ($result['cacheHit'] ?? false),
            'result' => $result,
        ]);
    }
}
