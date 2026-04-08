<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Middleware;

final class IdempotencyMiddleware
{
    /** @return array{headers: array<string, string>, query: array<string, mixed>, body: mixed, idemKey: ?string} */
    public function normalize(array $server, array $get, ?string $rawBody): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (!str_starts_with((string) $key, 'HTTP_')) {
                continue;
            }

            $name = strtolower(str_replace('_', '-', substr((string) $key, 5)));
            $headers[$name] = (string) $value;
        }

        $idemKey = $headers['x-idempotency-key'] ?? null;
        $body = null;
        if (null !== $rawBody && '' !== $rawBody) {
            $decoded = json_decode($rawBody, true);
            $body = is_array($decoded) ? $decoded : $rawBody;
        }

        return ['headers' => $headers, 'query' => $get, 'body' => $body, 'idemKey' => $idemKey];
    }
}
