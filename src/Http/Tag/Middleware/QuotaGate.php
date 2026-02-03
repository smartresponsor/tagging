<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag\Middleware;

use App\Service\Tag\RateLimiter;

final class QuotaGate
{
    public function __construct(private RateLimiter $limiter, private array $cfg = []) {}

    /** @param array{method:string,path:string,headers:array,body:string} $req */
    public function handle(array $req, callable $next): array
    {
        if (!$this->isProtected($req['path'] ?? '/')) {
            return $next($req);
        }
        if (!($this->cfg['enforce'] ?? true)) {
            return $next($req);
        }

        $tenant = (string)($req['headers']['X-Tenant-Id'] ?? $req['headers']['x-tenant-id'] ?? '');
        if ($tenant === '') {
            $tenant = 'tenant-unknown';
        }
        $route = $this->routeKey($req['method'] ?? 'GET', $req['path'] ?? '/');

        $g = $this->cfg['hard']['global'] ?? ['rps' => 1000, 'burst' => 2000];
        $pt = $this->cfg['hard']['per_tenant'] ?? ['rps' => 50, 'burst' => 100];
        $globalKey = 'global|' . $route;
        $tenantKey = 'tenant|' . $tenant . '|' . $route;

        $allowed = $this->limiter->allow($globalKey, (float)($g['rps'] ?? 1000), (int)($g['burst'] ?? 2000));
        if (!$allowed['ok']) {
            $this->bumpMetric('tag_ratelimit_throttled_total', ['scope' => 'global', 'route' => $route]);
            return $this->tooMany($allowed['retry_after'], 'rate_limit_global');
        }
        $allowed = $this->limiter->allow($tenantKey, (float)($pt['rps'] ?? 50), (int)($pt['burst'] ?? 100));
        if (!$allowed['ok']) {
            $this->bumpMetric('tag_ratelimit_throttled_total', ['scope' => 'tenant', 'route' => $route, 'tenant' => $tenant]);
            return $this->tooMany($allowed['retry_after'], 'rate_limit_tenant');
        }

        $op = $this->opFromPath($req['path'] ?? '/');
        $soft = $this->cfg['soft']['per_tenant'] ?? [];
        $limit = 0;
        if ($op === 'assign') {
            $limit = (int)($soft['assign_per_minute'] ?? 0);
        } elseif ($op === 'search') {
            $limit = (int)($soft['search_per_minute'] ?? 0);
        }

        if ($limit > 0) {
            $slotKey = 'soft|' . $tenant . '|' . $op;
            $res = $this->limiter->softAllow($slotKey, $limit, (int)($this->cfg['window_sec'] ?? 60));
            if (!$res['ok']) {
                $this->bumpMetric('tag_quota_exceeded_total', ['tenant' => $tenant, 'op' => $op]);
                return $this->tooMany((int)($res['retry_after'] ?? 1), 'quota_soft_exceeded');
            }
        }

        return $next($req);
    }

    private function isProtected(string $path): bool
    {
        $ignore = $this->cfg['paths']['ignore'] ?? ['/tag/_status', '/tag/_metrics'];
        foreach ($ignore as $pat) {
            if ($this->match($pat, $path)) {
                return false;
            }
        }
        $prot = $this->cfg['paths']['protected'] ?? ['/tag/**'];
        foreach ($prot as $pat) {
            if ($this->match($pat, $path)) {
                return true;
            }
        }
        return false;
    }

    private function match(string $pat, string $path): bool
    {
        $re = preg_quote($pat, '#');
        $re = str_replace(['\\*\\*', '\\*'], ['.*', '[^/]*'], $re);
        $re = '#^' . $re . '$#';
        return (bool)preg_match($re, $path);
    }

    private function routeKey(string $method, string $path): string
    {
        $norm = preg_replace('#/[A-Za-z0-9_-]+#', '/:id', $path, 1);
        return strtoupper($method) . ' ' . $norm;
    }

    private function opFromPath(string $path): string
    {
        if (preg_match('#^/tag/[^/]+/assign$#', $path)) {
            return 'assign';
        }
        if (preg_match('#^/tag/assign-bulk$#', $path)) {
            return 'assign';
        }
        if (preg_match('#^/tag/search#', $path)) {
            return 'search';
        }
        return 'other';
    }

    private function tooMany(int $retryAfter, string $code): array
    {
        return [
            429,
            ['Content-Type' => 'application/json', 'Retry-After' => (string)max(1, $retryAfter)],
            json_encode(['code' => $code]),
        ];
    }

    private function bumpMetric(string $name, array $labels): void
    {
        if (class_exists('App\\Ops\\Metrics\\TagMetrics')) {
            $exp = \App\Ops\Metrics\TagMetrics::exporter();
            if (method_exists($exp, 'inc')) {
                $exp->inc($name, $labels, 1);
            }
        }
    }
}
