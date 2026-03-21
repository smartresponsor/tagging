<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

final readonly class TagMiddlewareResponder
{
    /** @return array{0:int,1:array<string,string>,2:string} */
    public function reject(int $status, string $code, array $payload = [], array $headers = []): array
    {
        return [
            $status,
            $headers + [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ],
            json_encode(['ok' => false, 'code' => $code] + $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{"ok":false,"code":"encode_error"}',
        ];
    }
}
