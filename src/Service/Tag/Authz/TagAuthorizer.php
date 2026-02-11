<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag\Authz;

/**
 *
 */

/**
 *
 */
final class TagAuthorizer
{
    /** @var array<string, mixed> */
    private array $cfg;
    /** @var array<string, array<int, string>> */
    private array $ops;

    /**
     * @param array $cfg
     */
    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
        $this->ops = (array)($cfg['ops'] ?? []);
    }

    /** @param string[] $actorRoles */
    public function isAllowed(string $op, array $actorRoles): bool
    {
        if (!empty($this->cfg['fallback_allow_all'])) return true;
        $need = (array)($this->ops[$op] ?? []);
        if ($need === []) return true; // unknown op => allow by default
        foreach ($actorRoles as $r) {
            if (in_array($r, $need, true)) return true;
        }
        return false;
    }

    /**
     * @param string $method
     * @param string $path
     * @return string
     */
    public function detectOp(string $method, string $path): string
    {
        // Explicit overrides first
        foreach ((array)($this->cfg['path_overrides'] ?? []) as $ov) {
            $pref = (string)($ov['prefix'] ?? '');
            if ($pref !== '' && str_starts_with($path, $pref)) {
                return (string)($ov['op'] ?? 'read');
            }
        }
        // Basic inference: GET=read, HEAD=read, others=write
        $m = strtoupper($method);
        return in_array($m, ['GET', 'HEAD', 'OPTIONS'], true) ? 'read' : 'write';
    }

    /** @return string[] */
    public function parseRolesFromHeader(?string $csv): array
    {
        if ($csv === null || $csv === '') return [];
        $out = [];
        foreach (explode(',', $csv) as $p) {
            $r = trim($p);
            if ($r !== '') $out[] = $r;
        }
        return array_values(array_unique($out));
    }
}
