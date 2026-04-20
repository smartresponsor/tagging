<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag;

use App\Tagging\Http\Api\Tag\Responder\TagReadResponder;
use App\Tagging\Service\Core\Tag\SuggestService;

final readonly class SuggestController
{
    public function __construct(
        private SuggestService $svc,
        private TagReadResponder $responder = new TagReadResponder(),
    ) {}

    /**
     * @return array{0:int,1:array<string,string>,2:string}
     *
     * @throws \JsonException
     */
    public function get(array $req): array
    {
        $tenant = TagHttpRequest::tenantOrNull($req);
        if (null === $tenant) {
            return $this->responder->bad('invalid_tenant');
        }

        $result = $this->svc->suggest(
            $tenant,
            TagHttpRequest::queryString($req, 'q'),
            TagHttpRequest::queryInt($req, 'limit', 10, 1, 50),
        );

        return $this->responder->ok([
            'ok' => true,
            'items' => $result['items'] ?? [],
            'cacheHit' => (bool) ($result['cacheHit'] ?? false),
        ]);
    }
}
