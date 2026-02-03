<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Service\Security;

use App\Ops\Security\NonceStore;

final class HmacV2Verifier
{
    public function __construct(
        private string $secret,
        private int $skewSec = 120,
        private NonceStore $nonce = new NonceStore(),
    ){}

    /** @param array<string,string> $headers */
    public function verify(string $method, string $path, string $body, array $headers): array
    {
        $ts    = (string)($headers['X-SR-Timestamp'] ?? $headers['x-sr-timestamp'] ?? '');
        $nonce = (string)($headers['X-SR-Nonce'] ?? $headers['x-sr-nonce'] ?? '');
        $sig   = (string)($headers['X-SR-Signature'] ?? $headers['x-sr-signature'] ?? '');
        if ($ts === '' || $nonce === '' || $sig === '') {
            return ['ok'=>false,'code'=>401,'msg'=>'signature_missing'];
        }
        if (!ctype_digit($ts)) return ['ok'=>false,'code'=>401,'msg'=>'timestamp_invalid'];
        $its = (int)$ts;
        $now = time();
        if (abs($now - $its) > $this->skewSec) {
            return ['ok'=>false,'code'=>401,'msg'=>'timestamp_skew'];
        }
        $hash = hash('sha256', $body, false);
        $payload = $ts . "\n" . $nonce . "\n" . strtoupper($method) . "\n" . $path . "\n" . $hash;
        $calc = hash_hmac('sha256', $payload, $this->secret, false);
        if (!hash_equals($calc, strtolower($sig))) {
            return ['ok'=>false,'code'=>403,'msg'=>'signature_mismatch'];
        }
        // Replay guard
        if (!$this->nonce->putIfNew($nonce, $its)) {
            return ['ok'=>false,'code'=>409,'msg'=>'replay_detected'];
        }
        return ['ok'=>true,'code'=>200];
    }
}
