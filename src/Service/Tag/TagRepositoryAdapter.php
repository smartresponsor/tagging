<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;
use App\Domain\Tag\TagSynonym;
use App\ServiceInterface\Tag\TagPolicyRepositoryInterface;
use App\ServiceInterface\Tag\TagReadRepositoryInterface;
use App\ServiceInterface\Tag\TagRepositoryInterface;
use App\ServiceInterface\Tag\TagWriteRepositoryInterface;

/**
 *
 */

/**
 *
 */
final readonly class TagRepositoryAdapter implements TagRepositoryInterface
{
    /**
     * @param \App\ServiceInterface\Tag\TagWriteRepositoryInterface $tagWriteRepository
     * @param \App\ServiceInterface\Tag\TagReadRepositoryInterface $tagReadRepository
     * @param \App\ServiceInterface\Tag\TagPolicyRepositoryInterface $tagPolicyRepository
     */
    public function __construct(
        private TagWriteRepositoryInterface  $tagWriteRepository,
        private TagReadRepositoryInterface   $tagReadRepository,
        private TagPolicyRepositoryInterface $tagPolicyRepository
    )
    {
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\Tag $tag
     * @return void
     */
    public function saveTag(string $tenantId, Tag $tag): void
    {
        $this->tagWriteRepository->saveTag($tenantId, $tag);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @return \App\Domain\Tag\Tag|null
     */
    public function getById(string $tenantId, string $id): ?Tag
    {
        return $this->tagReadRepository->getById($tenantId, $id);
    }

    /**
     * @param string $tenantId
     * @param string $slug
     * @return \App\Domain\Tag\Tag|null
     */
    public function getBySlug(string $tenantId, string $slug): ?Tag
    {
        return $this->tagReadRepository->getBySlug($tenantId, $slug);
    }

    /**
     * @param string $tenantId
     * @param string|null $query
     * @param int $limit
     * @param int $offset
     * @return array|\App\Domain\Tag\Tag[]
     */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array
    {
        return $this->tagReadRepository->search($tenantId, $query, $limit, $offset);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @return void
     */
    public function deleteTag(string $tenantId, string $id): void
    {
        $this->tagWriteRepository->deleteTag($tenantId, $id);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagAssignment $a
     * @return void
     */
    public function saveAssignment(string $tenantId, TagAssignment $a): void
    {
        $this->tagWriteRepository->saveAssignment($tenantId, $a);
    }

    /**
     * @param string $tenantId
     * @param string $assignmentId
     * @return void
     */
    public function deleteAssignment(string $tenantId, string $assignmentId): void
    {
        $this->tagWriteRepository->deleteAssignment($tenantId, $assignmentId);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string|null $type
     * @param string|null $assignedId
     * @return array|\App\Domain\Tag\TagAssignment[]
     */
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array
    {
        return $this->tagReadRepository->listAssignments($tenantId, $tagId, $type, $assignedId);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagSynonym $s
     * @return void
     */
    public function saveSynonym(string $tenantId, TagSynonym $s): void
    {
        $this->tagWriteRepository->saveSynonym($tenantId, $s);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array|\App\Domain\Tag\TagSynonym[]
     */
    public function listSynonyms(string $tenantId, string $tagId): array
    {
        return $this->tagReadRepository->listSynonyms($tenantId, $tagId);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagRelation $r
     * @return void
     */
    public function saveRelation(string $tenantId, TagRelation $r): void
    {
        $this->tagWriteRepository->saveRelation($tenantId, $r);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string|null $type
     * @return array|\App\Domain\Tag\TagRelation[]
     */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array
    {
        return $this->tagReadRepository->listRelations($tenantId, $tagId, $type);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagScheme $s
     * @return void
     */
    public function saveScheme(string $tenantId, TagScheme $s): void
    {
        $this->tagWriteRepository->saveScheme($tenantId, $s);
    }

    /**
     * @param string $tenantId
     * @param string $name
     * @return \App\Domain\Tag\TagScheme|null
     */
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme
    {
        return $this->tagReadRepository->getSchemeByName($tenantId, $name);
    }

    /**
     * @param string $tenantId
     * @param string $fromTagId
     * @param string $toTagId
     * @return void
     */
    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void
    {
        $this->tagWriteRepository->reassignAssignments($tenantId, $fromTagId, $toTagId);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param bool $required
     * @param bool $modOnly
     * @return void
     */
    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void
    {
        $this->tagPolicyRepository->setTagFlags($tenantId, $tagId, $required, $modOnly);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $newLabel
     * @param string $newSlug
     * @return void
     */
    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void
    {
        $this->tagWriteRepository->renameTag($tenantId, $tagId, $newLabel, $newSlug);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $type
     * @param string $payloadJson
     * @return void
     */
    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void
    {
        $this->tagWriteRepository->insertProposal($tenantId, $id, $type, $payloadJson);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $status
     * @param string|null $decidedBy
     * @return void
     */
    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void
    {
        $this->tagWriteRepository->updateProposalStatus($tenantId, $id, $status, $decidedBy);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $action
     * @param string $entityType
     * @param string $entityId
     * @param string $detailsJson
     * @return void
     */
    public function insertAudit(string $tenantId, string $id, string $action, string $entityType, string $entityId, string $detailsJson): void
    {
        $this->tagWriteRepository->insertAudit($tenantId, $id, $action, $entityType, $entityId, $detailsJson);
    }

    /**
     * @param string $tenantId
     * @return array|\App\Domain\Tag\Tag[]
     */
    public function listAllTags(string $tenantId): array
    {
        return $this->tagReadRepository->listAllTags($tenantId);
    }

    /**
     * @param string $tenantId
     * @return array
     */
    public function getPolicy(string $tenantId): array
    {
        return $this->tagPolicyRepository->getPolicy($tenantId);
    }

    /**
     * @param string $tenantId
     * @param array $policy
     * @return void
     */
    public function setPolicy(string $tenantId, array $policy): void
    {
        $this->tagPolicyRepository->setPolicy($tenantId, $policy);
    }

    /**
     * @param string $tenantId
     * @param string $assignedType
     * @param int $limit
     * @return array|array[]
     */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array
    {
        return $this->tagReadRepository->facetTop($tenantId, $assignedType, $limit);
    }

    /**
     * @param string $tenantId
     * @param int $limit
     * @return array|array[]
     */
    public function tagCloud(string $tenantId, int $limit): array
    {
        return $this->tagReadRepository->tagCloud($tenantId, $limit);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $scope
     * @param string $refId
     * @param string $key
     * @param string $value
     * @return void
     */
    public function putClassification(string $tenantId, string $id, string $scope, string $refId, string $key, string $value): void
    {
        $this->tagWriteRepository->putClassification($tenantId, $id, $scope, $refId, $key, $value);
    }

    /**
     * @param string $tenantId
     * @param string $scope
     * @param string $refId
     * @return array|array[]
     */
    public function listClassifications(string $tenantId, string $scope, string $refId): array
    {
        return $this->tagReadRepository->listClassifications($tenantId, $scope, $refId);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $assignedType
     * @param string $assignedId
     * @param string $key
     * @param string $value
     * @param string $sourceScope
     * @param string $sourceId
     * @return void
     */
    public function putEffect(string $tenantId, string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void
    {
        $this->tagWriteRepository->putEffect($tenantId, $id, $assignedType, $assignedId, $key, $value, $sourceScope, $sourceId);
    }

    /**
     * @param string $tenantId
     * @param string $sourceScope
     * @param string $sourceId
     * @return void
     */
    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void
    {
        $this->tagWriteRepository->clearEffectsForSource($tenantId, $sourceScope, $sourceId);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array|array[]
     */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array
    {
        return $this->tagReadRepository->listAssignmentsByTag($tenantId, $tagId);
    }

    /**
     * @param string $tenantId
     * @param string $schemeName
     * @return array|array[]
     */
    public function listTagsByScheme(string $tenantId, string $schemeName): array
    {
        return $this->tagReadRepository->listTagsByScheme($tenantId, $schemeName);
    }
}
