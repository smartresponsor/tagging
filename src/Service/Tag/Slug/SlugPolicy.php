<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag\Slug;

use PDO;

/**
 *
 */

/**
 *
 */
final class SlugPolicy
{
    /** @param list<string> $reserved */
    public function __construct(
        private readonly PDO       $pdo,
        private readonly Slugifier $slugifier,
        private readonly array     $reserved = [],
        private readonly int       $maxLen = 64,
    )
    {
    }

    /**
     * @param string $tenant
     * @param string $source
     * @return string
     */
    public function make(string $tenant, string $source): string
    {
        $base = $this->slugifier->slugify($source);
        if ($base === '') $base = 'tag';
        if ($this->isReserved($base)) $base .= '-x';

        $slug = $base;
        $i = 1;
        while ($this->exists($tenant, $slug)) {
            $i++;
            $suffix = (string)$i;
            $cut = max(1, $this->maxLen - (1 + strlen($suffix)));
            $slug = substr($base, 0, $cut) . '-' . $suffix;
        }
        return $slug;
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function validate(string $slug): bool
    {
        $len = strlen($slug);
        if ($len < 2 || $len > $this->maxLen) return false;
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) return false;
        if ($this->isReserved($slug)) return false;
        return true;
    }

    /**
     * @param string $slug
     * @return bool
     */
    private function isReserved(string $slug): bool
    {
        return in_array($slug, $this->reserved, true);
    }

    /**
     * @param string $tenant
     * @param string $slug
     * @return bool
     */
    private function exists(string $tenant, string $slug): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM tag_entity WHERE tenant=:t AND slug=:s');
        $stmt->execute([':t' => $tenant, ':s' => $slug]);
        return (bool)$stmt->fetchColumn();
    }
}
