<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Service\Tag\Security;

use App\Service\Tag\Metric\TagMetrics;

final class NonceCache
{
    /** @var array<string,int> */
    private static array $map = [];
    private static int $ttl = 600;

    public static function configTtl(int $ttl): void { self::$ttl = max(60, $ttl); }

    public static function seen(string $nonce, int $now): bool
    {
        // purge expired occasionally
        foreach (self::$map as $n=>$exp) {
            if ($exp < $now) unset(self::$map[$n]);
        }
        if (isset(self::$map[$nonce]) && self::$map[$nonce] >= $now) {
            return true;
        }
        self::$map[$nonce] = $now + self::$ttl;
        return false;
    }
}

final class SignatureV2
{
    public function __construct(private array $cfg){}

    private function h(string $data, string $secret): string
    {
        return hash_hmac('sha256', $data, $secret);
    }

    private function canonical(string $method, string $path, string $ts, string $nonce, string $body): string
    {
        $bodyHash = hash('sha256', $body);
        return strtoupper($method) . "\n" . $path . "\n" . $ts . "\n" . $nonce . "\n" . $bodyHash;
    }

    public function verify(string $method, string $path, string $body, array $headers): void
    {
        $hSig = $headers[$this->hdr('signature')] ?? '';
        $hTs  = $headers[$this->hdr('timestamp')] ?? '';
        $hN   = $headers[$this->hdr('nonce')] ?? '';
        if ($hSig === '' || $hTs === '' || $hN === '') {
            TagMetrics::inc('tag_sig_fail_total', 1.0, ['reason'=>'missing']);
            throw new \RuntimeException('signature_missing'); // 401
        }
        if (!ctype_digit($hTs)) {
            TagMetrics::inc('tag_sig_fail_total', 1.0, ['reason'=>'ts_format']);
            throw new \RuntimeException('timestamp_invalid'); // 401
        }
        $now = time();
        $skew = (int)($this->cfg['hmac']['skew_seconds'] ?? 300);
        $ts = (int)$hTs;
        if (abs($now - $ts) > $skew) {
            TagMetrics::inc('tag_sig_fail_total', 1.0, ['reason'=>'ts_skew']);
            throw new \RuntimeException('timestamp_skew'); // 401
        }
        NonceCache::configTtl((int)($this->cfg['nonce_cache']['ttl_seconds'] ?? 600));
        if (NonceCache::seen($hN, $now)) {
            TagMetrics::inc('tag_sig_fail_total', 1.0, ['reason'=>'replay']);
            throw new \RuntimeException('replay_detected'); // 403
        }

        $data = $this->canonical($method, $path, (string)$ts, $hN, $body);
        $cur = (string)($this->cfg['hmac']['current'] ?? '');
        $prv = (string)($this->cfg['hmac']['previous'] ?? '');

        $ok = false;
        if ($cur !== '' && hash_equals($this->h($data, $cur), $hSig)) $ok = true;
        if (!$ok && $prv !== '' && hash_equals($this->h($data, $prv), $hSig)) $ok = true;

        if (!$ok) {
            TagMetrics::inc('tag_sig_fail_total', 1.0, ['reason'=>'mismatch']);
            throw new \RuntimeException('signature_invalid'); // 401
        }
        TagMetrics::inc('tag_sig_ok_total', 1.0);
    }

    private function hdr(string $k): string
    {
        $h = $this->cfg['headers'][$k] ?? null;
        return is_string($h) && $h !== '' ? $h : match($k){
            'signature' => 'X-SR-Signature',
            'timestamp' => 'X-SR-Timestamp',
            'nonce'     => 'X-SR-Nonce',
            default     => 'X-SR-'.$k,
        };
    }

    public function sign(string $method, string $path, string $body, string $secret, int $ts, string $nonce): string
    {
        $data = $this->canonical($method, $path, (string)$ts, $nonce, $body);
        return $this->h($data, $secret);
    }
}
