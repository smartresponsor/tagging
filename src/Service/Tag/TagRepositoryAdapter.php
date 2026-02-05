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

final class TagRepositoryAdapter implements TagRepositoryInterface
{
    public function __construct(
        private readonly TagWriteRepositoryInterface $tagWriteRepository,
        private readonly TagReadRepositoryInterface $tagReadRepository,
        private readonly TagPolicyRepositoryInterface $tagPolicyRepository,
        private readonly string $tenantId
    ) {
    }

    public function saveTag(Tag $tag): void
    {
        $this->tagWriteRepository->saveTag($this->tenantId, $tag);
    }

    public function getById(string $id): ?Tag
    {
        return $this->tagReadRepository->getById($this->tenantId, $id);
    }

    public function getBySlug(string $slug): ?Tag
    {
        return $this->tagReadRepository->getBySlug($this->tenantId, $slug);
    }

    public function search(?string $query, int $limit, int $offset): array
    {
        return $this->tagReadRepository->search($this->tenantId, $query, $limit, $offset);
    }

    public function deleteTag(string $id): void
    {
        $this->tagWriteRepository->deleteTag($this->tenantId, $id);
    }

    public function saveAssignment(TagAssignment $a): void
    {
        $this->tagWriteRepository->saveAssignment($this->tenantId, $a);
    }

    public function deleteAssignment(string $assignmentId): void
    {
        $this->tagWriteRepository->deleteAssignment($this->tenantId, $assignmentId);
    }

    public function listAssignments(string $tagId, ?string $type = null, ?string $assignedId = null): array
    {
        return $this->tagReadRepository->listAssignments($this->tenantId, $tagId, $type, $assignedId);
    }

    public function saveSynonym(TagSynonym $s): void
    {
        $this->tagWriteRepository->saveSynonym($this->tenantId, $s);
    }

    public function listSynonyms(string $tagId): array
    {
        return $this->tagReadRepository->listSynonyms($this->tenantId, $tagId);
    }

    public function saveRelation(TagRelation $r): void
    {
        $this->tagWriteRepository->saveRelation($this->tenantId, $r);
    }

    public function listRelations(string $tagId, ?string $type = null): array
    {
        return $this->tagReadRepository->listRelations($this->tenantId, $tagId, $type);
    }

    public function saveScheme(TagScheme $s): void
    {
        $this->tagWriteRepository->saveScheme($this->tenantId, $s);
    }

    public function getSchemeByName(string $name): ?TagScheme
    {
        return $this->tagReadRepository->getSchemeByName($this->tenantId, $name);
    }

    public function reassignAssignments(string $fromTagId, string $toTagId): void
    {
        $this->tagWriteRepository->reassignAssignments($this->tenantId, $fromTagId, $toTagId);
    }

    public function setTagFlags(string $tagId, bool $required, bool $modOnly): void
    {
        $this->tagPolicyRepository->setTagFlags($this->tenantId, $tagId, $required, $modOnly);
    }

    public function renameTag(string $tagId, string $newLabel, string $newSlug): void
    {
        $this->tagWriteRepository->renameTag($this->tenantId, $tagId, $newLabel, $newSlug);
    }

    public function insertProposal(string $id, string $type, string $payloadJson): void
    {
        $this->tagWriteRepository->insertProposal($this->tenantId, $id, $type, $payloadJson);
    }

    public function updateProposalStatus(string $id, string $status, ?string $decidedBy): void
    {
        $this->tagWriteRepository->updateProposalStatus($this->tenantId, $id, $status, $decidedBy);
    }

    public function insertAudit(string $id, string $action, string $entityType, string $entityId, string $detailsJson): void
    {
        $this->tagWriteRepository->insertAudit($this->tenantId, $id, $action, $entityType, $entityId, $detailsJson);
    }

    public function listAllTags(): array
    {
        return $this->tagReadRepository->listAllTags($this->tenantId);
    }

    public function getPolicy(): array
    {
        return $this->tagPolicyRepository->getPolicy($this->tenantId);
    }

    public function setPolicy(array $policy): void
    {
        $this->tagPolicyRepository->setPolicy($this->tenantId, $policy);
    }

    public function facetTop(string $assignedType, int $limit): array
    {
        return $this->tagReadRepository->facetTop($this->tenantId, $assignedType, $limit);
    }

    public function tagCloud(int $limit): array
    {
        return $this->tagReadRepository->tagCloud($this->tenantId, $limit);
    }

    public function putClassification(string $id, string $scope, string $refId, string $key, string $value): void
    {
        $this->tagWriteRepository->putClassification($this->tenantId, $id, $scope, $refId, $key, $value);
    }

    public function listClassifications(string $scope, string $refId): array
    {
        return $this->tagReadRepository->listClassifications($this->tenantId, $scope, $refId);
    }

    public function putEffect(
        string $id,
        string $assignedType,
        string $assignedId,
        string $key,
        string $value,
        string $sourceScope,
        string $sourceId
    ): void {
        $this->tagWriteRepository->putEffect(
            $this->tenantId,
            $id,
            $assignedType,
            $assignedId,
            $key,
            $value,
            $sourceScope,
            $sourceId
        );
    }

    public function clearEffectsForSource(string $sourceScope, string $sourceId): void
    {
        $this->tagWriteRepository->clearEffectsForSource($this->tenantId, $sourceScope, $sourceId);
    }

    public function listAssignmentsByTag(string $tagId): array
    {
        return $this->tagReadRepository->listAssignmentsByTag($this->tenantId, $tagId);
    }

    public function listTagsByScheme(string $schemeName): array
    {
        return $this->tagReadRepository->listTagsByScheme($this->tenantId, $schemeName);
    }
}
