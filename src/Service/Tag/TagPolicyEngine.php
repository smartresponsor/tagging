<?php
declare(strict_types=1);
namespace App\Service\Tag;

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
final class TagPolicyEngine {
    public function __construct(private array $policy){}

    public function validateTag(string $slug, string $label): void {
        $max = (int)($this->policy['maxLength'] ?? 255);
        if (mb_strlen($slug) > $max || mb_strlen($label) > $max) throw new \InvalidArgumentException('policy.maxLength');
        $allowedSlugs = $this->policy['allowedSlugs'] ?? [];
        if ($allowedSlugs && !in_array($slug, $allowedSlugs, true)) throw new \InvalidArgumentException('policy.allowedSlugs');
        $denied = $this->policy['deniedPrefixes'] ?? [];
        foreach ($denied as $p) if (str_starts_with($slug, $p)) throw new \InvalidArgumentException('policy.deniedPrefixes');
        $allowed = $this->policy['allowedPrefixes'] ?? [];
        if ($allowed) {
            $ok=false; foreach($allowed as $p) if (str_starts_with($slug,$p)) $ok=true;
            if (!$ok) throw new \InvalidArgumentException('policy.allowedPrefixes');
        }
    }
}
