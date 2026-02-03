<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

final class TagPropagationService {
    public function __construct(private TagRepositoryContract $repo){}

    public function putClassificationForTag(string $tagId, string $key, string $value): void {
        $this->repo->putClassification(UlidGenerator::generate(), 'tag', $tagId, $key, $value);
    }

    public function putClassificationForScheme(string $schemeName, string $key, string $value): void {
        $this->repo->putClassification(UlidGenerator::generate(), 'scheme', $schemeName, $key, $value);
    }

    public function replayForTag(string $tagId): int {
        $this->repo->clearEffectsForSource('tag', $tagId);
        $class = $this->repo->listClassifications('tag', $tagId);
        if (!$class) return 0;
        $pairs = $this->repo->listAssignmentsByTag($tagId);
        $n=0;
        foreach ($pairs as $p) {
            foreach ($class as $c) {
                $this->repo->putEffect(UlidGenerator::generate(), $p['assigned_type'], $p['assigned_id'], $c['key'], $c['value'], 'tag', $tagId);
                $n++;
            }
        }
        return $n;
    }

    public function replayForScheme(string $schemeName): int {
        $this->repo->clearEffectsForSource('scheme', $schemeName);
        $class = $this->repo->listClassifications('scheme', $schemeName);
        if (!$class) return 0;
        $tags = $this->repo->listTagsByScheme($schemeName);
        $n=0;
        foreach ($tags as $t) {
            $pairs = $this->repo->listAssignmentsByTag($t['tag_id']);
            foreach ($pairs as $p) {
                foreach ($class as $c) {
                    $this->repo->putEffect(UlidGenerator::generate(), $p['assigned_type'], $p['assigned_id'], $c['key'], $c['value'], 'scheme', $schemeName);
                    $n++;
                }
            }
        }
        return $n;
    }

    public function dryRunForTag(string $tagId): array {
        $class = $this->repo->listClassifications('tag', $tagId);
        $pairs = $this->repo->listAssignmentsByTag($tagId);
        $out=[];
        foreach ($pairs as $p) foreach ($class as $c) $out[] = $p['assigned_type'].':'.$p['assigned_id'].' -> '.$c['key'].'='.$c['value'];
        return $out;
    }
}
