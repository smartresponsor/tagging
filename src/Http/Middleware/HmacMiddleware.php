<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Middleware;

final class HmacMiddleware
{
    public function verify(array $headers, string $rawBody): bool
    {
        $secret = getenv('SR_HMAC_SECRET') ?: '';
        if ($secret === '') return true; // no-op if not configured
        $nonce = $headers['x-sr-nonce'] ?? '';
        $sig = $headers['x-sr-signature'] ?? '';
        if ($nonce === '' || $sig === '') return false;
        $calc = base64_encode(hash_hmac('sha256', $nonce.'|'+$rawBody, $secret, true));
        // constant-time compare
        if (strlen($calc) !== strlen($sig)) return false;
        $res = 0; for ($i=0; $i<strlen($calc); $i++) { $res |= ord($calc[$i]) ^ ord($sig[$i]); }
        return $res === 0;
    }
}
