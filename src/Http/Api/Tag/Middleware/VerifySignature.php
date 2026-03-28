<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Middleware;

use App\Http\Api\Tag\Responder\TagMiddlewareResponder;
use App\Service\Security\HmacV2Verifier;

final readonly class VerifySignature
{
    private const DEFAULT_INCLUDE = ['/tag/**'];
    private const DEFAULT_EXCLUDE = ['/tag/_status', '/tag/_surface', '/tag/_metrics'];

    public function __construct(
        private HmacV2Verifier $verifier,
        private array $cfg = [],
        private TagMiddlewareResponder $responder = new TagMiddlewareResponder(),
    ) {}

    /** @param array{method:string,path:string,headers:array,body:string} $req */
    public function handle(array $req, callable $next): array
    {
        $path = (string) ($req['path'] ?? '/');
        if (!$this->isEnabled() || !$this->shouldApply($path)) {
            return $next($req);
        }

        $result = $this->verifier->verify(
            (string) ($req['method'] ?? 'GET'),
            $path,
            (string) ($req['body'] ?? ''),
            is_array($req['headers'] ?? null) ? $req['headers'] : [],
        );

        if (($result['ok'] ?? false) === true) {
            return $next($req);
        }

        return $this->reject((int) ($result['code'] ?? 401), (string) ($result['msg'] ?? 'signature_invalid'));
    }

    private function isEnabled(): bool
    {
        return (bool) ($this->cfg['enforce'] ?? false) && '' !== trim((string) ($this->cfg['secret'] ?? ''));
    }

    private function shouldApply(string $path): bool
    {
        foreach ($this->excludePatterns() as $pat) {
            if ($this->match((string) $pat, $path)) {
                return false;
            }
        }
        foreach ($this->includePatterns() as $pat) {
            if ($this->match((string) $pat, $path)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function includePatterns(): array
    {
        $patterns = $this->cfg['apply']['include'] ?? self::DEFAULT_INCLUDE;

        return is_array($patterns) ? array_values(array_map('strval', $patterns)) : self::DEFAULT_INCLUDE;
    }

    /** @return list<string> */
    private function excludePatterns(): array
    {
        $patterns = $this->cfg['apply']['exclude'] ?? self::DEFAULT_EXCLUDE;

        return is_array($patterns) ? array_values(array_map('strval', $patterns)) : self::DEFAULT_EXCLUDE;
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    private function reject(int $status, string $code): array
    {
        $headers = 401 === $status ? ['WWW-Authenticate' => 'HMAC-SHA256'] : [];

        return $this->responder->reject($status, $code, [], $headers);
    }

    private function match(string $pat, string $path): bool
    {
        if (str_ends_with($pat, '/**')) {
            $base = substr($pat, 0, -3);

            return $path === $base || str_starts_with($path, $base . '/');
        }

        $re = preg_quote($pat, '#');
        $re = str_replace(['\*\*', '\*'], ['.*', '[^/]*'], $re);
        $re = '#^' . $re . '$#';

        return (bool) preg_match($re, $path);
    }
}
