<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\Domain\Tag\TagRelation;

final class TagGraph {
    /** @param array<string, TagRelation[]> $adj */
    public static function wouldCreateCycle(string $from, string $to, array $adj): bool {
        // DFS from 'to' to see if we can reach 'from'
        $stack = [$to];
        $visited = [];
        while ($stack) {
            $cur = array_pop($stack);
            if (isset($visited[$cur])) continue;
            $visited[$cur] = true;
            if ($cur === $from) return true;
            foreach ($adj[$cur] ?? [] as $rel) {
                if ($rel->type() !== 'broader') continue;
                $stack[] = $rel->toTagId();
            }
        }
        return false;
    }
}
