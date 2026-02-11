<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Ops\Security;

/**
 *
 */

/**
 *
 */
final class NonceStore
{
    private string $dir;
    private int $ttl;
    private int $max;

    /**
     * @param string $dir
     * @param int $ttlSec
     * @param int $max
     */
    public function __construct(string $dir = 'var/cache/nonce', int $ttlSec = 300, int $max = 100000)
    {
        $this->dir = rtrim($dir, '/');
        $this->ttl = max(1, $ttlSec);
        $this->max = max(1, $max);

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    /** returns true if nonce is new and stored; false if seen (valid window) */
    public function putIfNew(string $nonce, int $ts): bool
    {
        $key = $this->key($nonce, $ts);
        $path = $this->dir . '/' . $key;
        $now = time();

        // GC occasionally
        if (mt_rand(0, 99) === 0) {
            $this->gc($now);
        }

        if (is_file($path)) {
            // if entry exists and not yet expired -> replay
            $raw = file_get_contents($path);
            $exp = is_string($raw) ? (int)trim($raw) : 0;
            if ($exp > $now) {
                return false;
            }
        }

        // write new expiry
        file_put_contents($path, (string)($ts + $this->ttl), LOCK_EX);
        return true;
    }

    /**
     * @param string $nonce
     * @param int $ts
     * @return string
     */
    private function key(string $nonce, int $ts): string
    {
        return substr(hash('sha256', $nonce . '|' . $ts), 0, 40);
    }

    /**
     * @param int $now
     * @return void
     */
    private function gc(int $now): void
    {
        if (!is_dir($this->dir)) {
            return;
        }

        $files = scandir($this->dir);
        if (!is_array($files)) {
            return;
        }

        $n = 0;
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }

            $p = $this->dir . '/' . $f;
            if (!is_file($p)) {
                continue;
            }

            $n++;
            $raw = file_get_contents($p);
            $exp = is_string($raw) ? (int)trim($raw) : 0;
            if ($exp <= $now) {
                unlink($p);
            }
        }

        // soft cap: trim oldest files if above max
        if ($n <= $this->max) {
            return;
        }

        $pairs = [];
        $files2 = scandir($this->dir);
        if (!is_array($files2)) {
            return;
        }

        foreach ($files2 as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }

            $p = $this->dir . '/' . $f;
            if (!is_file($p)) {
                continue;
            }

            $mt = filemtime($p);
            $pairs[$p] = is_int($mt) ? $mt : 0;
        }

        asort($pairs);
        $toDel = $n - $this->max;
        for ($i = 0; $i < $toDel; $i++) {
            $p = array_key_first($pairs);
            if (!$p) {
                break;
            }

            if (is_file($p)) {
                unlink($p);
            }

            unset($pairs[$p]);
        }
    }
}
