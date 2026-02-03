<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Http\Middleware;

final class IdempotencyMiddleware
{
    /** @return array{headers:array<string,string>,query:array<string,mixed>,body:mixed,idemKey:?string} */
    public function normalize(array $server, array $get, ?string $rawBody): array
    {
        $headers = [];
        foreach ($server as $k=>$v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name = strtolower(str_replace('_','-',substr($k,5)));
                $headers[$name] = (string)$v;
            }
        }
        $idemKey = $headers['x-idempotency-key'] ?? null;
        $body = null;
        if ($rawBody !== null && $rawBody !== '') {
            $tmp = json_decode($rawBody, true);
            $body = is_array($tmp) ? $tmp : $rawBody;
        }
        return ['headers'=>$headers,'query'=>$get,'body'=>$body,'idemKey'=>$idemKey];
    }
}
