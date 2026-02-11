<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;
use InvalidArgumentException;
use RuntimeException;

/**
 *
 */

/**
 *
 */
final class TagQuotaService
{
    /**
     * @param \App\ServiceInterface\Tag\TagRepositoryInterface $repo
     */
    public function __construct(private readonly TagRepositoryContract $repo)
    {
    }

    /**
     * @param string $actorId
     * @param string $op
     * @return void
     * @throws \Random\RandomException
     */
    public function checkRate(string $actorId, string $op): void
    {
        $cfg = $this->repo->getQuotaConfig();
        $perMin = (int)($cfg['per_minute'] ?? 60);
        $n = $this->repo->countWritesInWindow($actorId, 60);
        if ($n >= $perMin) throw new RuntimeException('rate_limited');
        $this->repo->logWrite(UlidGenerator::generate(), $actorId, $op);
    }

    /**
     * @param string $type
     * @param string $id
     * @return void
     */
    public function ensureEntityCap(string $type, string $id): void
    {
        $cfg = $this->repo->getQuotaConfig();
        $cap = (int)($cfg['max_tags_per_entity'] ?? 250);
        $n = $this->repo->countAssignmentsForEntity($type, $id);
        if ($n >= $cap) throw new InvalidArgumentException('entity_tag_cap');
    }
}
