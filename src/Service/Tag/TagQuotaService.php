<?php
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

final class TagQuotaService {
    public function __construct(private TagRepositoryContract $repo){}
    public function checkRate(string $actorId, string $op): void {
        $cfg = $this->repo->getQuotaConfig();
        $perMin = (int)($cfg['per_minute'] ?? 60);
        $n = $this->repo->countWritesInWindow($actorId, 60);
        if ($n >= $perMin) throw new \RuntimeException('rate_limited');
        $this->repo->logWrite(UlidGenerator::generate(), $actorId, $op);
    }
    public function ensureEntityCap(string $type, string $id): void {
        $cfg = $this->repo->getQuotaConfig();
        $cap = (int)($cfg['max_tags_per_entity'] ?? 250);
        $n = $this->repo->countAssignmentsForEntity($type, $id);
        if ($n >= $cap) throw new \InvalidArgumentException('entity_tag_cap');
    }
}
