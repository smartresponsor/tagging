<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag\Middleware;

use App\Service\Tag\Authz\TagAuthorizer;

/**
 *
 */

/**
 *
 */
final readonly class Authorize
{
    /**
     * @param \App\Service\Tag\Authz\TagAuthorizer $auth
     * @param array $cfg
     */
    public function __construct(private TagAuthorizer $auth, private array $cfg)
    {
    }

    /**
     * Framework-agnostic example:
     * $request = ['method','path','headers'=>['X-Actor-Id'=>..., 'X-Roles'=>...]]
     * returns array [code, headers, body] on deny or delegates to $next
     */
    public function handle(array $request, callable $next): array
    {
        $hdrs = (array)($request['headers'] ?? []);
        $hRoles = (string)($hdrs[$this->hdr()] ?? '');

        $roles = $this->auth->parseRolesFromHeader($hRoles);
        $op = $this->auth->detectOp((string)($request['method'] ?? 'GET'), (string)($request['path'] ?? '/'));

        if (!$this->auth->isAllowed($op, $roles)) {
            return [403, ['Content-Type' => 'application/json'], json_encode(['code' => 'forbidden', 'op' => $op])];
        }
        return $next($request);
    }

    /**
     * @return string
     */
    private function hdr(): string
    {
        $h = $this->cfg['headers']['roles'] ?? null;
        return is_string($h) && $h !== '' ? $h : match ('roles') {
            'actor' => 'X-Actor-Id',
            'roles' => 'X-Roles',
            default => 'X-SR-' . 'roles',
        };
    }
}
