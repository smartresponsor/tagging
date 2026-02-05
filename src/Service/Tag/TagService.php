<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagSynonym;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;

final class TagService {
    public function __construct(
        private TagRepositoryContract $repo,
        private TagConfig $cfg = new TagConfig()
    ){}

    public function create(string $tenantId, ?string $slugOrNull, string $label): Tag {
        $label = TagNormalizer::normalizeLabel($label);
        $slug = ($slugOrNull === '' || $slugOrNull === null) ? TagNormalizer::slugify($label) : TagNormalizer::slugify($slugOrNull);
        $this->validateLengths($slug, $label);
        if ($this->repo->getBySlug($tenantId, $slug)) throw new \InvalidArgumentException("Slug already exists");
        $tag = Tag::create(UlidGenerator::generate(), $slug, $label);
        $this->repo->saveTag($tenantId, $tag);
        return $tag;
    }
    public function list(string $tenantId, ?string $q, int $limit = 20, int $offset = 0): array { return $this->repo->search($tenantId, $q, $limit, $offset); }
    public function delete(string $tenantId, string $id): void { $this->repo->deleteTag($tenantId, $id); }

    public function assign(string $tenantId, string $tagId, string $type, string $assignedId): TagAssignment {
        $this->enforceCaps($tenantId, $tagId, $type, $assignedId);
        $a = TagAssignment::create(UlidGenerator::generate(), $tagId, $type, $assignedId);
        $this->repo->saveAssignment($tenantId, $a);
        return $a;
    }

    public function addSynonym(string $tenantId, string $tagId, string $label): TagSynonym {
        $label = TagNormalizer::normalizeLabel($label);
        $s = TagSynonym::create(UlidGenerator::generate(), $tagId, $label);
        $this->repo->saveSynonym($tenantId, $s);
        return $s;
    }

    public function addRelation(string $tenantId, string $fromTagId, string $toTagId, string $type): TagRelation {
        if ($type === 'broader') {
            $adj = [];
            $all = $this->repo->listRelations($tenantId, $toTagId, 'broader');
            $adj[$toTagId] = $all;
            if (TagGraph::wouldCreateCycle($fromTagId, $toTagId, $adj)) throw new \InvalidArgumentException('broader cycle');
        }
        $r = TagRelation::create(UlidGenerator::generate(), $fromTagId, $toTagId, $type);
        $this->repo->saveRelation($tenantId, $r);
        return $r;
    }

    public function createScheme(string $tenantId, string $name, ?string $locale): TagScheme {
        if ($this->repo->getSchemeByName($tenantId, $name)) throw new \InvalidArgumentException('scheme exists');
        $s = TagScheme::create(UlidGenerator::generate(), $name, $locale);
        $this->repo->saveScheme($tenantId, $s);
        return $s;
    }

    private function validateLengths(string $slug, string $label): void {
        if (mb_strlen($slug) > $this->cfg->maxTagLength) throw new \InvalidArgumentException("slug too long");
        if (mb_strlen($label) > $this->cfg->maxTagLength) throw new \InvalidArgumentException("label too long");
        if ($slug === '' || $label === '') throw new \InvalidArgumentException("slug/label must not be empty");
    }
    private function enforceCaps(string $tenantId, string $tagId, string $type, string $assignedId): void {
        $current = $this->repo->listAssignments($tenantId, $tagId, $type, $assignedId);
        if (count($current) >= 1) {
            throw new \InvalidArgumentException('assignment_exists');
        }
    }

    private function loadPolicyEngine(string $tenantId): TagPolicyEngine
    {
        $policy = $this->repo->getPolicy($tenantId);
        return new TagPolicyEngine($policy);
    }

}
