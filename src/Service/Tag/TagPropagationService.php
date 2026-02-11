<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

/**
 *
 */

/**
 *
 */
final readonly class TagPropagationService
{
    /**
     * @param \App\ServiceInterface\Tag\TagRepositoryInterface $repo
     */
    public function __construct(private TagRepositoryContract $repo)
    {
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $key
     * @param string $value
     * @return void
     * @throws \Random\RandomException
     */
    public function putClassificationForTag(string $tenantId, string $tagId, string $key, string $value): void
    {
        $this->repo->putClassification($tenantId, UlidGenerator::generate(), 'tag', $tagId, $key, $value);
    }

    /**
     * @param string $tenantId
     * @param string $schemeName
     * @param string $key
     * @param string $value
     * @return void
     * @throws \Random\RandomException
     */
    public function putClassificationForScheme(string $tenantId, string $schemeName, string $key, string $value): void
    {
        $this->repo->putClassification($tenantId, UlidGenerator::generate(), 'scheme', $schemeName, $key, $value);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return int
     * @throws \Random\RandomException
     */
    public function replayForTag(string $tenantId, string $tagId): int
    {
        $this->repo->clearEffectsForSource($tenantId, 'tag', $tagId);
        $class = $this->repo->listClassifications($tenantId, 'tag', $tagId);
        if (!$class) return 0;
        $pairs = $this->repo->listAssignmentsByTag($tenantId, $tagId);
        $n = 0;
        foreach ($pairs as $p) {
            foreach ($class as $c) {
                $this->repo->putEffect($tenantId, UlidGenerator::generate(), $p['assigned_type'], $p['assigned_id'], $c['key'], $c['value'], 'tag', $tagId);
                $n++;
            }
        }
        return $n;
    }

    /**
     * @param string $tenantId
     * @param string $schemeName
     * @return int
     * @throws \Random\RandomException
     */
    public function replayForScheme(string $tenantId, string $schemeName): int
    {
        $this->repo->clearEffectsForSource($tenantId, 'scheme', $schemeName);
        $class = $this->repo->listClassifications($tenantId, 'scheme', $schemeName);
        if (!$class) return 0;
        $tags = $this->repo->listTagsByScheme($tenantId, $schemeName);
        $n = 0;
        foreach ($tags as $t) {
            $pairs = $this->repo->listAssignmentsByTag($tenantId, $t['tag_id']);
            foreach ($pairs as $p) {
                foreach ($class as $c) {
                    $this->repo->putEffect($tenantId, UlidGenerator::generate(), $p['assigned_type'], $p['assigned_id'], $c['key'], $c['value'], 'scheme', $schemeName);
                    $n++;
                }
            }
        }
        return $n;
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array
     */
    public function dryRunForTag(string $tenantId, string $tagId): array
    {
        $class = $this->repo->listClassifications($tenantId, 'tag', $tagId);
        $pairs = $this->repo->listAssignmentsByTag($tenantId, $tagId);
        $out = [];
        foreach ($pairs as $p) foreach ($class as $c) $out[] = $p['assigned_type'] . ':' . $p['assigned_id'] . ' -> ' . $c['key'] . '=' . $c['value'];
        return $out;
    }
}
