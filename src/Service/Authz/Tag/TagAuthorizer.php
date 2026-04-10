<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Authz\Tag;

use App\Service\Core\Tag\Authz\TagAuthorizer as CoreTagAuthorizer;

/**
 * Backward-compatible facade over the canonical core authorizer.
 */
final class TagAuthorizer
{
    private CoreTagAuthorizer $inner;

    public function __construct(array $cfg)
    {
        $this->inner = new CoreTagAuthorizer($cfg);
    }

    /** @param string[] $actorRoles */
    public function isAllowed(string $op, array $actorRoles): bool
    {
        return $this->inner->isAllowed($op, $actorRoles);
    }

    public function detectOp(string $method, string $path): string
    {
        return $this->inner->detectOp($method, $path);
    }

    /** @return string[] */
    public function parseRolesFromHeader(?string $csv): array
    {
        return $this->inner->parseRolesFromHeader($csv);
    }
}
