<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag;

final class TagHttpRequest
{
    public static function headers(array $req): array
    {
        return is_array($req['headers'] ?? null) ? $req['headers'] : [];
    }

    public static function query(array $req): array
    {
        return is_array($req['query'] ?? null) ? $req['query'] : [];
    }

    public static function body(array $req): array
    {
        return is_array($req['body'] ?? null) ? $req['body'] : [];
    }

    public static function tenant(array $req): string
    {
        $headers = self::headers($req);

        return trim((string) ($headers['X-Tenant-Id'] ?? $headers['x-tenant-id'] ?? ''));
    }
}
