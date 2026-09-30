<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Entity\Tag\TagSchemeEntity;
use App\Tagging\Entity\Tag\TagSynonymEntity;
use App\Tagging\DTO\Write\TagAuditRecord;
use App\Tagging\DTO\Write\TagClassificationRecord;
use App\Tagging\DTO\Write\TagEffectRecord;
use App\Tagging\RepositoryInterface\TagPolicyRepositoryInterface;
use App\Tagging\RepositoryInterface\TagReadRepositoryInterface;
use App\Tagging\RepositoryInterface\TagRepositoryInterface;
use App\Tagging\RepositoryInterface\TagWriteRepositoryInterface;

final readonly class TagRepositoryAdapter implements TagRepositoryInterface
{
    public function __construct(
        private TagWriteRepositoryInterface $tagWriteRepository,
        private TagReadRepositoryInterface $tagReadRepository,
        private TagPolicyRepositoryInterface $tagPolicyRepository,
    ) {}

    public function saveTag(string $vendorId, TagEntity $tag): void
    {
        $this->tagWriteRepository->saveTag($vendorId, $tag);
    }

    public function getById(string $vendorId, string $id): ?TagEntity
    {
        return $this->tagReadRepository->getById($vendorId, $id);
    }

    public function getBySlug(string $vendorId, string $slug): ?TagEntity
    {
        return $this->tagReadRepository->getBySlug($vendorId, $slug);
    }

    public function existsSlug(string $vendorId, string $slug, ?string $excludeTagId = null): bool
    {
        $existing = $this->tagReadRepository->getBySlug($vendorId, $slug);
        if (null === $existing) {
            return false;
        }

        return $existing->id() !== $excludeTagId;
    }

    public function i18nSlugExists(string $vendorId, string $locale, string $slug, ?string $excludeTagId = null): bool
    {
        return $this->existsSlug($vendorId, $slug, $excludeTagId);
    }

    /**
     * @return array|Tag[]
     */
    public function search(string $vendorId, ?string $query, int $limit, int $offset): array
    {
        return $this->tagReadRepository->search($vendorId, $query, $limit, $offset);
    }

    public function deleteTag(string $vendorId, string $id): void
    {
        $this->tagWriteRepository->deleteTag($vendorId, $id);
    }

    public function saveAssignment(string $vendorId, TagAssignmentEntity $a): void
    {
        $this->tagWriteRepository->saveAssignment($vendorId, $a);
    }

    public function deleteAssignment(string $vendorId, string $assignmentId): void
    {
        $this->tagWriteRepository->deleteAssignment($vendorId, $assignmentId);
    }

    /**
     * @return array|TagAssignment[]
     */
    public function listAssignments(
        string $vendorId,
        string $tagId,
        ?string $type = null,
        ?string $assignedId = null,
    ): array {
        return $this->tagReadRepository->listAssignments($vendorId, $tagId, $type, $assignedId);
    }

    public function saveSynonym(string $vendorId, TagSynonymEntity $s): void
    {
        $this->tagWriteRepository->saveSynonym($vendorId, $s);
    }

    /**
     * @return array|TagSynonym[]
     */
    public function listSynonyms(string $vendorId, string $tagId): array
    {
        return $this->tagReadRepository->listSynonyms($vendorId, $tagId);
    }

    public function saveRelation(string $vendorId, TagRelationEntity $r): void
    {
        $this->tagWriteRepository->saveRelation($vendorId, $r);
    }

    /**
     * @return array|TagRelation[]
     */
    public function listRelations(string $vendorId, string $tagId, ?string $type = null): array
    {
        return $this->tagReadRepository->listRelations($vendorId, $tagId, $type);
    }

    public function saveScheme(string $vendorId, TagSchemeEntity $s): void
    {
        $this->tagWriteRepository->saveScheme($vendorId, $s);
    }

    public function getSchemeByName(string $vendorId, string $nameEntity): ?TagSchemeEntity
    {
        return $this->tagReadRepository->getSchemeByName($vendorId, $nameEntity);
    }

    public function reassignAssignments(string $vendorId, string $fromTagId, string $toTagId): void
    {
        $this->tagWriteRepository->reassignAssignments($vendorId, $fromTagId, $toTagId);
    }

    public function setTagFlags(string $vendorId, string $tagId, bool $required, bool $modOnly): void
    {
        $this->tagPolicyRepository->setTagFlags($vendorId, $tagId, $required, $modOnly);
    }

    public function renameTag(string $vendorId, string $tagId, string $newLabel, string $newSlug): void
    {
        $this->tagWriteRepository->renameTag($vendorId, $tagId, $newLabel, $newSlug);
    }

    public function insertProposal(string $vendorId, string $id, string $type, string $payloadJson): void
    {
        $this->tagWriteRepository->insertProposal($vendorId, $id, $type, $payloadJson);
    }

    public function updateProposalStatus(string $vendorId, string $id, string $status, ?string $decidedBy): void
    {
        $this->tagWriteRepository->updateProposalStatus($vendorId, $id, $status, $decidedBy);
    }

    public function insertAudit(string $vendorId, TagAuditRecord $record): void
    {
        $this->tagWriteRepository->insertAudit($vendorId, $record);
    }

    /**
     * @return array|Tag[]
     */
    public function listAllTags(string $vendorId): array
    {
        return $this->tagReadRepository->listAllTags($vendorId);
    }

    public function countTags(string $vendorId): int
    {
        return $this->tagReadRepository->countTags($vendorId);
    }

    public function countAssignments(string $vendorId): int
    {
        return $this->tagReadRepository->countAssignments($vendorId);
    }

    public function getPolicy(string $vendorId): array
    {
        return $this->tagPolicyRepository->getPolicy($vendorId);
    }

    public function setPolicy(string $vendorId, array $policy): void
    {
        $this->tagPolicyRepository->setPolicy($vendorId, $policy);
    }

    /**
     * @return array|array[]
     */
    public function facetTop(string $vendorId, string $assignedType, int $limit): array
    {
        return $this->tagReadRepository->facetTop($vendorId, $assignedType, $limit);
    }

    /**
     * @return array|array[]
     */
    public function tagCloud(string $vendorId, int $limit): array
    {
        return $this->tagReadRepository->tagCloud($vendorId, $limit);
    }

    public function putClassification(string $vendorId, TagClassificationRecord $record): void
    {
        $this->tagWriteRepository->putClassification($vendorId, $record);
    }

    /**
     * @return array|array[]
     */
    public function listClassifications(string $vendorId, string $scope, string $refId): array
    {
        return $this->tagReadRepository->listClassifications($vendorId, $scope, $refId);
    }

    public function putEffect(string $vendorId, TagEffectRecord $record): void
    {
        $this->tagWriteRepository->putEffect($vendorId, $record);
    }

    public function clearEffectsForSource(string $vendorId, string $sourceScope, string $sourceId): void
    {
        $this->tagWriteRepository->clearEffectsForSource($vendorId, $sourceScope, $sourceId);
    }

    /**
     * @return array|array[]
     */
    public function listAssignmentsByTag(string $vendorId, string $tagId): array
    {
        return $this->tagReadRepository->listAssignmentsByTag($vendorId, $tagId);
    }

    /**
     * @return array|array[]
     */
    public function listTagsByScheme(string $vendorId, string $schemeName): array
    {
        return $this->tagReadRepository->listTagsByScheme($vendorId, $schemeName);
    }
}
