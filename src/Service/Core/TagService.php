<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagEntityAssignment;
use App\Tagging\Entity\Tag\TagEntityRelation;
use App\Tagging\Entity\Tag\TagEntityScheme;
use App\Tagging\Entity\Tag\TagEntitySynonym;
use App\Tagging\Service\Core\TagRepositoryInterface as TagRepositoryContract;
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
    public function create(string $tenantId, ?string $slugOrNull, string $label): TagEntity
    {
        $label = TagNormalizer::normalizeLabel($label);
        $slug = ('' === $slugOrNull || null === $slugOrNull)
            ? TagNormalizer::slugify($label)
            : TagNormalizer::slugify($slugOrNull);
        $this->validateLengths($slug, $label);
        if ($this->repo->getBySlug($tenantId, $slug)) {
            throw new \InvalidArgumentException('Slug already exists');
        }
        $tag = TagEntity::create($tenantId, TagUlidGenerator::generate(), $slug, $label);
        $this->repo->saveTag($tenantId, $tag);

        return $tag;
    }

    public function list(string $tenantId, ?string $q, int $limit = 20, int $offset = 0): array
    {
        return $this->repo->search($tenantId, $q, $limit, $offset);
    }

    public function delete(string $tenantId, string $id): void
    {
        $this->repo->deleteTag($tenantId, $id);
    }

    /**
     * @throws RandomException
     */
    public function assign(string $tenantId, string $tagId, string $type, string $assignedId): TagAssignmentEntity
    {
        $this->enforceCaps($tenantId, $tagId, $type, $assignedId);
        $a = TagAssignmentEntity::create($tenantId, TagUlidGenerator::generate(), $tagId, $type, $assignedId);
        $this->repo->saveAssignment($tenantId, $a);

        return $a;
    }

    /**
     * @throws RandomException
     */
    public function addSynonym(string $tenantId, string $tagId, string $label): TagSynonymEntity
    {
        $label = TagNormalizer::normalizeLabel($label);
        $s = TagSynonymEntity::create($tenantId, TagUlidGenerator::generate(), $tagId, $label);
        $this->repo->saveSynonym($tenantId, $s);

        return $s;
    }

    /**
     * @throws RandomException
     */
    public function addRelation(string $tenantId, string $fromTagId, string $toTagId, string $type): TagRelationEntity
    {
        if ('broader' === $type) {
            $adj = [];
            $all = $this->repo->listRelations($tenantId, $toTagId, 'broader');
            $adj[$toTagId] = $all;
            if (TagGraph::wouldCreateCycle($fromTagId, $toTagId, $adj)) {
                throw new \InvalidArgumentException('broader cycle');
            }
        }
        $r = TagRelationEntity::create($tenantId, TagUlidGenerator::generate(), $fromTagId, $toTagId, $type);
        $this->repo->saveRelation($tenantId, $r);

        return $r;
    }

    /**
     * @throws RandomException
     */
    public function createScheme(string $tenantId, string $nameEntity, ?string $locale): TagSchemeEntity
    {
        if ($this->repo->getSchemeByName($tenantId, $nameEntity)) {
            throw new \InvalidArgumentException('scheme exists');
        }
        $s = TagSchemeEntity::create($tenantId, TagUlidGenerator::generate(), $nameEntity, $locale);
        $this->repo->saveScheme($tenantId, $s);

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

    private function enforceCaps(string $tenantId, string $tagId, string $type, string $assignedId): void
    {
        $current = $this->repo->listAssignments($tenantId, $tagId, $type, $assignedId);
        if (count($current) >= 1) {
            throw new \InvalidArgumentException('assignment_exists');
        }
    }
}
