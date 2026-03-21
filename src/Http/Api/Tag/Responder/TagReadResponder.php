<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

/**
 * Canonical JSON responder for host-minimal read endpoints.
 */
final class TagReadResponder
{
    /** @return array{0:int,1:array<string,string>,2:string} */
    public function ok(array $body, int $status = 200): array
    {
        return [
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}',
        ];
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function bad(string $code, int $status = 400, array $extra = []): array
    {
        return $this->ok(['ok' => false, 'code' => $code] + $extra, $status);
    }
}
