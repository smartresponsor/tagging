<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\DTO\Write\TagClassificationRecord;
use App\Tagging\DTO\Write\TagEffectRecord;
use App\Tagging\RepositoryInterface\TagRepositoryInterface as TagRepositoryContract;
use Random\RandomException;

final readonly class TagPropagationService
{
    public function __construct(private TagRepositoryContract $repo) {}

    /**
     * @throws RandomException
     */
    public function putClassificationForTag(string $vendorId, string $tagId, string $key, string $value): void
    {
        $this->repo->putClassification(
            $vendorId,
            new TagClassificationRecord(TagUlidGenerator::generate(), 'tag', $tagId, $key, $value),
        );
    }

    /**
     * @throws RandomException
     */
    public function putClassificationForScheme(string $vendorId, string $schemeName, string $key, string $value): void
    {
        $this->repo->putClassification(
            $vendorId,
            new TagClassificationRecord(TagUlidGenerator::generate(), 'scheme', $schemeName, $key, $value),
        );
    }

    /**
     * @throws RandomException
     */
    public function replayForTag(string $vendorId, string $tagId): int
    {
        $this->repo->clearEffectsForSource($vendorId, 'tag', $tagId);
        $class = $this->repo->listClassifications($vendorId, 'tag', $tagId);
        if (!$class) {
            return 0;
        }
        $pairs = $this->repo->listAssignmentsByTag($vendorId, $tagId);
        $n = 0;
        foreach ($pairs as $p) {
            foreach ($class as $c) {
                $this->repo->putEffect(
                    $vendorId,
                    new TagEffectRecord(
                        TagUlidGenerator::generate(),
                        $p['assigned_type'],
                        $p['assigned_id'],
                        $c['key'],
                        $c['value'],
                        'tag',
                        $tagId,
                    ),
                );
                ++$n;
            }
        }

        return $n;
    }

    /**
     * @throws RandomException
     */
    public function replayForScheme(string $vendorId, string $schemeName): int
    {
        $this->repo->clearEffectsForSource($vendorId, 'scheme', $schemeName);
        $class = $this->repo->listClassifications($vendorId, 'scheme', $schemeName);
        if (!$class) {
            return 0;
        }
        $tags = $this->repo->listTagsByScheme($vendorId, $schemeName);
        $n = 0;
        foreach ($tags as $t) {
            $pairs = $this->repo->listAssignmentsByTag($vendorId, $t['tag_id']);
            foreach ($pairs as $p) {
                foreach ($class as $c) {
                    $this->repo->putEffect(
                        $vendorId,
                        new TagEffectRecord(
                            TagUlidGenerator::generate(),
                            $p['assigned_type'],
                            $p['assigned_id'],
                            $c['key'],
                            $c['value'],
                            'scheme',
                            $schemeName,
                        ),
                    );
                    ++$n;
                }
            }
        }

        return $n;
    }

    public function dryRunForTag(string $vendorId, string $tagId): array
    {
        $class = $this->repo->listClassifications($vendorId, 'tag', $tagId);
        $pairs = $this->repo->listAssignmentsByTag($vendorId, $tagId);
        $out = [];
        foreach ($pairs as $p) {
            foreach ($class as $c) {
                $out[] = $p['assigned_type'] . ':' . $p['assigned_id'] . ' -> ' . $c['key'] . '=' . $c['value'];
            }
        }

        return $out;
    }
}
