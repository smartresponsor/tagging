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
        $tenant = $this->tenant($req);
        if (null === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $query = TagHttpRequest::query($req);
        $result = $this->svc->suggest(
            $tenant,
            (string) ($query['q'] ?? ''),
            $this->boundedInt($query['limit'] ?? 10, 10, 1, 50),
        );

        return $this->responder->ok([
            'ok' => true,
            'items' => $result['items'] ?? [],
            'cacheHit' => (bool) ($result['cacheHit'] ?? false),
            'result' => $result,
        ]);
    }

    private function tenant(array $request): ?string
    {
        $tenant = TagHttpRequest::tenant($request);

        return '' !== $tenant ? $tenant : null;
    }

    private function boundedInt(mixed $value, int $default, int $min, int $max): int
    {
        return max($min, min($max, (int) ($value ?? $default)));
    }
}
