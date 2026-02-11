<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag;

use App\Service\Tag\SynonymService;

/**
 *
 */

/**
 *
 */
final class SynonymController
{
    /**
     * @param \App\Service\Tag\SynonymService $svc
     */
    public function __construct(private readonly SynonymService $svc = new SynonymService())
    {
    }

    public function list(array $params): array
    {
        $tagId = (string)($params['id'] ?? '');
        if ($tagId === '') return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        $res = $this->svc->list($tagId);
        return [200, ['Content-Type' => 'application/json'], json_encode($res)];
    }

    /**
     * @param array $params
     * @param array $body
     * @return array
     */
    public function add(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $label = (string)($body['label'] ?? '');
        if ($tagId === '' || $label === '') return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        $res = $this->svc->add($tagId, $label);
        return [200, ['Content-Type' => 'application/json'], json_encode($res)];
    }

    /**
     * @param array $params
     * @param array $body
     * @return array
     */
    public function remove(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $label = (string)($body['label'] ?? '');
        if ($tagId === '' || $label === '') return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        $res = $this->svc->remove($tagId, $label);
        return [200, ['Content-Type' => 'application/json'], json_encode($res)];
    }
}
