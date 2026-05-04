<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag\Middleware;

use App\Tagging\Service\Core\TagTenantGuard;

final readonly class TagTenantContextMiddleware
{
    public function __construct(private TagTenantGuard $guard) {}

    /** @param array{headers:array} $req */
    public function handle(array $req, callable $next): array
    {
        $tenant = $this->guard->requireTenant($req['headers'] ?? []);
        // Inject tenant in request for downstream handlers
        $req['tenantId'] = $tenant;

        return $next($req);
    }
}
