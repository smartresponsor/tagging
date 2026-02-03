<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Http\Middleware;

final class HmacMiddleware
{
    public function verify(array $headers, string $rawBody): bool
    {
        $secret = getenv('SR_HMAC_SECRET') ?: '';
        if ($secret === '') return true; // no-op if not configured
        $normalized = [];
        foreach ($headers as $key => $value) {
            $name = strtolower((string)$key);
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $normalized[$name] = (string)$value;
        }
        $nonce = $normalized['x-sr-nonce'] ?? '';
        $sig = $normalized['x-sr-signature'] ?? '';
        if ($nonce === '' || $sig === '') return false;
        $calc = base64_encode(hash_hmac('sha256', $nonce.'|'.$rawBody, $secret, true));
        return hash_equals($calc, $sig);
    }
}
