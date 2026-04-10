<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Authz\Tag;

final class TagAuthorizer
{
    /** @var array<string, mixed> */
    private array $cfg;
    /** @var array<string, array<int, string>> */
    private array $ops;

    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
        $this->ops = (array) ($cfg['ops'] ?? []);
    }

    /** @param string[] $actorRoles */
    public function isAllowed(string $op, array $actorRoles): bool
    {
        if (!empty($this->cfg['fallback_allow_all'])) {
            return true;
        }
        $need = (array) ($this->ops[$op] ?? []);
        if ([] === $need) {
            return true;
        } // unknown op => allow by default

        return array_any($actorRoles, fn ($r) => in_array($r, $need, true));
    }

    public function detectOp(string $method, string $path): string
    {
        // Explicit overrides first
        foreach ((array) ($this->cfg['path_overrides'] ?? []) as $override) {
            $prefix = $this->nonEmptyString($override['prefix'] ?? null);
            if (null !== $prefix && str_starts_with($path, $prefix)) {
                return $this->stringOrDefault($override['op'] ?? null, 'read');
            }
        }
        // Basic inference: GET=read, HEAD=read, others=write
        $m = strtoupper($method);

        return in_array($m, ['GET', 'HEAD', 'OPTIONS'], true) ? 'read' : 'write';
    }

    /** @return string[] */
    public function parseRolesFromHeader(?string $csv): array
    {
        $value = $this->nonEmptyString($csv);
        if (null === $value) {
            return [];
        }

        $out = [];
        foreach (explode(',', $value) as $part) {
            $role = $this->nonEmptyString($part);
            if (null !== $role) {
                $out[] = $role;
            }
        }

        return array_values(array_unique($out));
    }

    private function nonEmptyString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }

    private function stringOrDefault(mixed $value, string $default): string
    {
        return is_string($value) && '' !== $value ? $value : $default;
    }
}
