<?php
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagSynonym;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
final class TagService {
    public function __construct(
        private TagRepositoryContract $repo,
        private TagConfig $cfg = new TagConfig()
    ){}

    public function create(string $slugOrNull, string $label): Tag {
        $label = TagNormalizer::normalizeLabel($label);
        $slug = ($slugOrNull === '' || $slugOrNull === null) ? TagNormalizer::slugify($label) : TagNormalizer::slugify($slugOrNull);
        $this->validateLengths($slug, $label);
        if ($this->repo->getBySlug($slug)) throw new \InvalidArgumentException("Slug already exists");
        $tag = Tag::create(UlidGenerator::generate(), $slug, $label);
        $this->repo->saveTag($tag);
        return $tag;
    }
    public function list(?string $q, int $limit = 20, int $offset = 0): array { return $this->repo->search($q, $limit, $offset); }
    public function delete(string $id): void { $this->repo->deleteTag($id); }

    public function assign(string $tagId, string $type, string $assignedId): TagAssignment {
        $this->enforceCaps($tagId, $type, $assignedId);
        $a = TagAssignment::create(UlidGenerator::generate(), $tagId, $type, $assignedId);
        $this->repo->saveAssignment($a);
        return $a;
    }

    public function addSynonym(string $tagId, string $label): TagSynonym {
        $label = TagNormalizer::normalizeLabel($label);
        $s = TagSynonym::create(UlidGenerator::generate(), $tagId, $label);
        $this->repo->saveSynonym($s);
        return $s;
    }

    public function addRelation(string $fromTagId, string $toTagId, string $type): TagRelation {
        if ($type === 'broader') {
            // prevent cycles
            $adj = [];
            $all = $this->repo->listRelations($toTagId, 'broader');
            $adj[$toTagId] = $all;
            if (TagGraph::wouldCreateCycle($fromTagId, $toTagId, $adj)) throw new \InvalidArgumentException('broader cycle');
        }
        $r = TagRelation::create(UlidGenerator::generate(), $fromTagId, $toTagId, $type);
        $this->repo->saveRelation($r);
        return $r;
    }

    public function createScheme(string $name, ?string $locale): TagScheme {
        if ($this->repo->getSchemeByName($name)) throw new \InvalidArgumentException('scheme exists');
        $s = TagScheme::create(UlidGenerator::generate(), $name, $locale);
        $this->repo->saveScheme($s);
        return $s;
    }

    private function validateLengths(string $slug, string $label): void {
        if (mb_strlen($slug) > $this->cfg->maxTagLength) throw new \InvalidArgumentException("slug too long");
        if (mb_strlen($label) > $this->cfg->maxTagLength) throw new \InvalidArgumentException("label too long");
        if ($slug === '' || $label === '') throw new \InvalidArgumentException("slug/label must not be empty");
    }
    private function enforceCaps(string $tagId, string $type, string $assignedId): void {
        $current = $this->repo->listAssignments($tagId, $type, $assignedId);
        if (count($current) >= 1) return;
    }

    // E5 policy hooks
    private function loadPolicyEngine(): TagPolicyEngine
    {
        $policy = $this->repo->getPolicy();
        return new TagPolicyEngine($policy);
    }

}
