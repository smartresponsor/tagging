<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Middleware;

use App\Http\Api\Tag\Responder\TagMiddlewareResponder;
use App\Service\Security\HmacV2Verifier;

final readonly class VerifySignature
{
    public function __construct(
        private HmacV2Verifier $verifier,
        private array $cfg = [],
        private TagMiddlewareResponder $responder = new TagMiddlewareResponder(),
    ) {
    }

    /** @param array{method:string,path:string,headers:array,body:string} $req */
    public function handle(array $req, callable $next): array
    {
        if (!$this->isEnabled() || !$this->shouldApply((string) ($req['path'] ?? '/'))) {
            return $next($req);
        }

        $res = $this->verifier->verify(
            (string) ($req['method'] ?? 'GET'),
            (string) ($req['path'] ?? '/'),
            (string) ($req['body'] ?? ''),
            is_array($req['headers'] ?? null) ? $req['headers'] : [],
        );

        if (($res['ok'] ?? false) === true) {
            return $next($req);
        }

        $status = (int) ($res['code'] ?? 401);
        $code = (string) ($res['msg'] ?? 'signature_invalid');
        $headers = [];
        if (401 === $status) {
            $headers['WWW-Authenticate'] = 'HMAC-SHA256';
        }

        return $this->responder->reject($status, $code, [], $headers);
    }

    private function isEnabled(): bool
    {
        return (bool) ($this->cfg['enforce'] ?? false) && '' !== trim((string) ($this->cfg['secret'] ?? ''));
    }

    private function shouldApply(string $path): bool
    {
        $inc = $this->cfg['apply']['include'] ?? ['/tag/**'];
        $exc = $this->cfg['apply']['exclude'] ?? ['/tag/_status', '/tag/_surface', '/tag/_metrics'];
        foreach ($exc as $pat) {
            if ($this->match((string) $pat, $path)) {
                return false;
            }
        }
        foreach ($inc as $pat) {
            if ($this->match((string) $pat, $path)) {
                return true;
            }
        }

        return false;
    }

    private function match(string $pat, string $path): bool
    {
        if (str_ends_with($pat, '/**')) {
            $base = substr($pat, 0, -3);

            return $path === $base || str_starts_with($path, $base.'/');
        }

        $re = preg_quote($pat, '#');
        $re = str_replace(['\*\*', '\*'], ['.*', '[^/]*'], $re);
        $re = '#^'.$re.'$#';

        return (bool) preg_match($re, $path);
    }
}
