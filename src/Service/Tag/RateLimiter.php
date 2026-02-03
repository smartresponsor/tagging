<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

final class RateLimiter
{
    /** @var array<string,array{tokens:float,ts:float,burst:int,rps:float}> */
    private array $buckets = [];

    /** @var array<string,array{window:int,count:int,limit:int,window_sec:int}> */
    private array $windows = [];

    /** @return array{ok:bool,retry_after:int} */
    public function allow(string $key, float $rps, int $burst): array
    {
        $now = microtime(true);
        $bucket = $this->buckets[$key] ?? ['tokens' => (float)$burst, 'ts' => $now, 'burst' => $burst, 'rps' => $rps];
        $elapsed = max(0.0, $now - $bucket['ts']);
        $tokens = min((float)$burst, $bucket['tokens'] + ($elapsed * $rps));

        if ($tokens < 1.0) {
            $retryAfter = (int)max(1, ceil((1.0 - $tokens) / max($rps, 0.001)));
            $this->buckets[$key] = ['tokens' => $tokens, 'ts' => $now, 'burst' => $burst, 'rps' => $rps];
            return ['ok' => false, 'retry_after' => $retryAfter];
        }

        $tokens -= 1.0;
        $this->buckets[$key] = ['tokens' => $tokens, 'ts' => $now, 'burst' => $burst, 'rps' => $rps];
        return ['ok' => true, 'retry_after' => 0];
    }

    /** @return array{ok:bool,retry_after:int} */
    public function softAllow(string $key, int $limit, int $windowSec): array
    {
        $windowSec = max(1, $windowSec);
        $now = time();
        $window = (int)floor($now / $windowSec);
        $entry = $this->windows[$key] ?? ['window' => $window, 'count' => 0, 'limit' => $limit, 'window_sec' => $windowSec];

        if ($entry['window'] !== $window) {
            $entry = ['window' => $window, 'count' => 0, 'limit' => $limit, 'window_sec' => $windowSec];
        }

        if ($entry['count'] >= $limit) {
            $retryAfter = ($entry['window'] + 1) * $windowSec - $now;
            $this->windows[$key] = $entry;
            return ['ok' => false, 'retry_after' => (int)max(1, $retryAfter)];
        }

        $entry['count']++;
        $this->windows[$key] = $entry;
        return ['ok' => true, 'retry_after' => 0];
    }
}
