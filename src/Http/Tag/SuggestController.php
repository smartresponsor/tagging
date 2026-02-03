<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Tag;

use App\Service\Tag\SuggestService;

final class SuggestController
{
    public function __construct(private SuggestService $svc) {}

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req): array
    {
        $headers = $req['headers'] ?? [];
        $tenant = (string)($headers['X-Tenant-Id'] ?? $headers['x-tenant-id'] ?? '');
        $q = (string)($req['query']['q'] ?? '');
        $limit = (int)($req['query']['limit'] ?? 10);
        if ($tenant === '' || $q === '') return [400, ['Content-Type'=>'application/json'], json_encode(['ok'=>false,'code'=>'validation_failed'])];
        $res = $this->svc->suggest($tenant, $q, $limit);
        return [200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true,'result'=>$res])];
    }
}
