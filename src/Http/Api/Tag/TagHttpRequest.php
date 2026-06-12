<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag;

final class TagHttpRequest
{
    public static function header(array $req, string $nameEntity, string $default = ''): string
    {
        $headers = self::headers($req);

        return trim((string) ($headers[$nameEntity] ?? $headers[strtolower($nameEntity)] ?? $default));
    }

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

    public static function queryString(array $req, string $nameEntity, string $fallback = ''): string
    {
        $query = self::query($req);

        return trim((string) ($query[$nameEntity] ?? ('' !== $fallback ? ($query[$fallback] ?? '') : '')));
    }

    public static function queryInt(array $req, string $nameEntity, int $default, int $min, int $max): int
    {
        $query = self::query($req);

        return max($min, min($max, (int) ($query[$nameEntity] ?? $default)));
    }

    public static function bodyString(array $req, string $nameEntity, string $fallback = ''): string
    {
        $body = self::body($req);

        return trim((string) ($body[$nameEntity] ?? ('' !== $fallback ? ($body[$fallback] ?? '') : '')));
    }

    public static function tenantOrNull(array $req): ?string
    {
        $tenant = self::tenant($req);

        return '' !== $tenant ? $tenant : null;
    }

    public static function tenant(array $req): string
    {
        return self::header($req, 'X-Tenant-Id');
    }
}
