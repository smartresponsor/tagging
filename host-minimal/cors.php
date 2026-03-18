<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * @return array<string,string>
 */
function tag_cors_headers(?string $requestOrigin, ?string $configuredOrigin): array
{
    $configured = trim((string) ($configuredOrigin ?? '*'));
    $origin = trim((string) ($requestOrigin ?? ''));

    if ($configured === '') {
        $configured = '*';
    }

    $allowOrigin = '*';
    if ($configured === '*') {
        $allowOrigin = '*';
    } elseif ($origin !== '' && $origin !== 'null' && $origin === $configured) {
        $allowOrigin = $origin;
    } else {
        $allowOrigin = $configured;
    }

    return [
        'Access-Control-Allow-Origin' => $allowOrigin,
        'Access-Control-Allow-Methods' => 'GET,POST,PATCH,DELETE,OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type,X-Tenant-Id,X-Idempotency-Key,X-SR-Timestamp,X-SR-Nonce,X-SR-Signature',
        'Access-Control-Max-Age' => '600',
        'Vary' => 'Origin',
    ];
}
