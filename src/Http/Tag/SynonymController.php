<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Http\Tag;

use App\Service\Tag\SynonymService;

final class SynonymController
{
    public function __construct(private SynonymService $svc = new SynonymService()){}

    public function list(array $params): array
    {
        $tagId = (string)($params['id'] ?? '');
        if ($tagId === '') return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>'bad_request'])];
        $res = $this->svc->list($tagId);
        return [200, ['Content-Type'=>'application/json'], json_encode($res)];
    }

    public function add(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $label = (string)($body['label'] ?? '');
        if ($tagId === '' || $label === '') return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>'bad_request'])];
        $res = $this->svc->add($tagId, $label);
        return [200, ['Content-Type'=>'application/json'], json_encode($res)];
    }

    public function remove(array $params, array $body): array
    {
        $tagId = (string)($params['id'] ?? '');
        $label = (string)($body['label'] ?? '');
        if ($tagId === '' || $label === '') return [400, ['Content-Type'=>'application/json'], json_encode(['code'=>'bad_request'])];
        $res = $this->svc->remove($tagId, $label);
        return [200, ['Content-Type'=>'application/json'], json_encode($res)];
    }
}
