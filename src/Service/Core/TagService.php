<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Normalizer\Core\TagNormalizer;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Entity\Tag\TagSchemeEntity;
use App\Tagging\Entity\Tag\TagSynonymEntity;
use App\Tagging\RepositoryInterface\TagRepositoryInterface as TagRepositoryContract;
use Random\RandomException;

final readonly class TagService
{
    public function __construct(
        private TagRepositoryContract $repo,
        private TagConfig $cfg = new TagConfig(),
    ) {}

    /**
     * @throws RandomException
     */
    public function create(string $vendorId, ?string $slugOrNull, string $label): TagEntity
    {
        $label = TagNormalizer::normalizeLabel($label);
        $slug = ('' === $slugOrNull || null === $slugOrNull)
            ? TagNormalizer::slugify($label)
            : TagNormalizer::slugify($slugOrNull);
        $this->validateLengths($slug, $label);
        if ($this->repo->getBySlug($vendorId, $slug)) {
            throw new \InvalidArgumentException('Slug already exists');
        }
        $tag = TagEntity::create($vendorId, TagUlidGenerator::generate(), $slug, $label);
        $this->repo->saveTag($vendorId, $tag);

        return $tag;
    }

    public function list(string $vendorId, ?string $q, int $limit = 20, int $offset = 0): array
    {
        return $this->repo->search($vendorId, $q, $limit, $offset);
    }

    public function delete(string $vendorId, string $id): void
    {
        $this->repo->deleteTag($vendorId, $id);
    }

    /**
     * @throws RandomException
     */
    public function assign(string $vendorId, string $tagId, string $type, string $assignedId): TagAssignmentEntity
    {
        $this->enforceCaps($vendorId, $tagId, $type, $assignedId);
        $a = TagAssignmentEntity::create($vendorId, TagUlidGenerator::generate(), $tagId, $type, $assignedId);
        $this->repo->saveAssignment($vendorId, $a);

        return $a;
    }

    /**
     * @throws RandomException
     */
    public function addSynonym(string $vendorId, string $tagId, string $label): TagSynonymEntity
    {
        $label = TagNormalizer::normalizeLabel($label);
        $s = TagSynonymEntity::create($vendorId, TagUlidGenerator::generate(), $tagId, $label);
        $this->repo->saveSynonym($vendorId, $s);

        return $s;
    }

    /**
     * @throws RandomException
     */
    public function addRelation(string $vendorId, string $fromTagId, string $toTagId, string $type): TagRelationEntity
    {
        if ('broader' === $type) {
            $adj = [];
            $all = $this->repo->listRelations($vendorId, $toTagId, 'broader');
            $adj[$toTagId] = $all;
            if (TagGraph::wouldCreateCycle($fromTagId, $toTagId, $adj)) {
                throw new \InvalidArgumentException('broader cycle');
            }
        }
        $r = TagRelationEntity::create($vendorId, TagUlidGenerator::generate(), $fromTagId, $toTagId, $type);
        $this->repo->saveRelation($vendorId, $r);

        return $r;
    }

    /**
     * @throws RandomException
     */
    public function createScheme(string $vendorId, string $nameEntity, ?string $locale): TagSchemeEntity
    {
        if ($this->repo->getSchemeByName($vendorId, $nameEntity)) {
            throw new \InvalidArgumentException('scheme exists');
        }
        $s = TagSchemeEntity::create($vendorId, TagUlidGenerator::generate(), $nameEntity, $locale);
        $this->repo->saveScheme($vendorId, $s);

        return $s;
    }

    private function validateLengths(string $slug, string $label): void
    {
        if (mb_strlen($slug) > $this->cfg->maxTagLength) {
            throw new \InvalidArgumentException('slug too long');
        }
        if (mb_strlen($label) > $this->cfg->maxTagLength) {
            throw new \InvalidArgumentException('label too long');
        }
        if ('' === $slug || '' === $label) {
            throw new \InvalidArgumentException('slug/label must not be empty');
        }
    }

    private function enforceCaps(string $vendorId, string $tagId, string $type, string $assignedId): void
    {
        $current = $this->repo->listAssignments($vendorId, $tagId, $type, $assignedId);
        if (count($current) >= 1) {
            throw new \InvalidArgumentException('assignment_exists');
        }
    }
}
