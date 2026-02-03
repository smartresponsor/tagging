<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\Http\Tag\Middleware;

use App\Service\Tag\TenantGuard;

final class TenantContext
{
    public function __construct(private TenantGuard $guard){}

    /** @param array{headers:array} $req */
    public function handle(array $req, callable $next): array
    {
        $tenant = $this->guard->requireTenant($req['headers'] ?? []);
        // Inject tenant in request for downstream handlers
        $req['tenantId'] = $tenant;
        return $next($req);
    }
}
