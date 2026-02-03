<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Http\Tag\Middleware;

use App\Service\Security\HmacV2Verifier;

final class VerifySignature
{
    public function __construct(private HmacV2Verifier $verifier, private array $cfg = []) {}

    /** @param array{method:string,path:string,headers:array,body:string} $req */
    public function handle(array $req, callable $next): array
    {
        if (!$this->shouldApply($req['path'] ?? '/')) {
            return $next($req);
        }
        $res = $this->verifier->verify($req['method'] ?? 'GET', $req['path'] ?? '/', $req['body'] ?? '', $req['headers'] ?? []);
        if (!($res['ok'] ?? false)) {
            $code = (int)($res['code'] ?? 401);
            return [$code, ['Content-Type'=>'application/json'], json_encode(['code'=>$res['msg'] ?? 'forbidden'])];
        }
        return $next($req);
    }

    private function shouldApply(string $path): bool
    {
        $inc = $this->cfg['apply']['include'] ?? ['/tag/**'];
        $exc = $this->cfg['apply']['exclude'] ?? ['/tag/_status','/tag/_metrics'];
        foreach ($exc as $pat) if ($this->match($pat, $path)) return false;
        foreach ($inc as $pat) if ($this->match($pat, $path)) return true;
        return false;
    }

    private function match(string $pat, string $path): bool
    {
        // Very small glob matcher: ** matches anything, * matches any except slash
        $re = preg_quote($pat, '#');
        $re = str_replace(['\*\*','\*'], ['.*','[^/]*'], $re);
        $re = '#^' . $re . '$#';
        return (bool)preg_match($re, $path);
    }
}
