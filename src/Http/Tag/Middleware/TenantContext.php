<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag\Middleware;

use App\Service\Tag\TenantGuard;

/**
 *
 */

/**
 *
 */
final readonly class TenantContext
{
    /**
     * @param \App\Service\Tag\TenantGuard $guard
     */
    public function __construct(private TenantGuard $guard)
    {
    }

    /** @param array{headers:array} $req */
    public function handle(array $req, callable $next): array
    {
        $tenant = $this->guard->requireTenant($req['headers'] ?? []);
        // Inject tenant in request for downstream handlers
        $req['tenantId'] = $tenant;
        return $next($req);
    }
}
