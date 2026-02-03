<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Tag;

use App\Service\Tag\SearchService;

final class SearchController
{
    public function __construct(private SearchService $svc) {}

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function get(array $req): array
    {
        $headers = $req['headers'] ?? [];
        $tenant = (string)($headers['X-Tenant-Id'] ?? $headers['x-tenant-id'] ?? '');
        $q = (string)($req['query']['q'] ?? '');
        $ps = (int)($req['query']['pageSize'] ?? 20);
        $pt = (string)($req['query']['pageToken'] ?? '');
        if ($tenant === '' || $q === '') return [400, ['Content-Type'=>'application/json'], json_encode(['ok'=>false,'code'=>'validation_failed'])];
        $res = $this->svc->search($tenant, $q, $ps, $pt ?: null);
        return [200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true,'result'=>$res])];
    }
}
