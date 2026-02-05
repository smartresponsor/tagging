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
        private readonly TagPolicyRepositoryInterface $tagPolicyRepository
    ) {
    }

    public function saveTag(string $tenantId, Tag $tag): void { $this->tagWriteRepository->saveTag($tenantId, $tag); }
    public function getById(string $tenantId, string $id): ?Tag { return $this->tagReadRepository->getById($tenantId, $id); }
    public function getBySlug(string $tenantId, string $slug): ?Tag { return $this->tagReadRepository->getBySlug($tenantId, $slug); }
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array { return $this->tagReadRepository->search($tenantId, $query, $limit, $offset); }
    public function deleteTag(string $tenantId, string $id): void { $this->tagWriteRepository->deleteTag($tenantId, $id); }
    public function saveAssignment(string $tenantId, TagAssignment $a): void { $this->tagWriteRepository->saveAssignment($tenantId, $a); }
    public function deleteAssignment(string $tenantId, string $assignmentId): void { $this->tagWriteRepository->deleteAssignment($tenantId, $assignmentId); }
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array { return $this->tagReadRepository->listAssignments($tenantId, $tagId, $type, $assignedId); }
    public function saveSynonym(string $tenantId, TagSynonym $s): void { $this->tagWriteRepository->saveSynonym($tenantId, $s); }
    public function listSynonyms(string $tenantId, string $tagId): array { return $this->tagReadRepository->listSynonyms($tenantId, $tagId); }
    public function saveRelation(string $tenantId, TagRelation $r): void { $this->tagWriteRepository->saveRelation($tenantId, $r); }
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array { return $this->tagReadRepository->listRelations($tenantId, $tagId, $type); }
    public function saveScheme(string $tenantId, TagScheme $s): void { $this->tagWriteRepository->saveScheme($tenantId, $s); }
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme { return $this->tagReadRepository->getSchemeByName($tenantId, $name); }
    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void { $this->tagWriteRepository->reassignAssignments($tenantId, $fromTagId, $toTagId); }
    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void { $this->tagPolicyRepository->setTagFlags($tenantId, $tagId, $required, $modOnly); }
    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void { $this->tagWriteRepository->renameTag($tenantId, $tagId, $newLabel, $newSlug); }
    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void { $this->tagWriteRepository->insertProposal($tenantId, $id, $type, $payloadJson); }
    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void { $this->tagWriteRepository->updateProposalStatus($tenantId, $id, $status, $decidedBy); }
    public function insertAudit(string $tenantId, string $id, string $action, string $entityType, string $entityId, string $detailsJson): void { $this->tagWriteRepository->insertAudit($tenantId, $id, $action, $entityType, $entityId, $detailsJson); }
    public function listAllTags(string $tenantId): array { return $this->tagReadRepository->listAllTags($tenantId); }
    public function getPolicy(string $tenantId): array { return $this->tagPolicyRepository->getPolicy($tenantId); }
    public function setPolicy(string $tenantId, array $policy): void { $this->tagPolicyRepository->setPolicy($tenantId, $policy); }
    public function facetTop(string $tenantId, string $assignedType, int $limit): array { return $this->tagReadRepository->facetTop($tenantId, $assignedType, $limit); }
    public function tagCloud(string $tenantId, int $limit): array { return $this->tagReadRepository->tagCloud($tenantId, $limit); }
    public function putClassification(string $tenantId, string $id, string $scope, string $refId, string $key, string $value): void { $this->tagWriteRepository->putClassification($tenantId, $id, $scope, $refId, $key, $value); }
    public function listClassifications(string $tenantId, string $scope, string $refId): array { return $this->tagReadRepository->listClassifications($tenantId, $scope, $refId); }
    public function putEffect(string $tenantId, string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void { $this->tagWriteRepository->putEffect($tenantId, $id, $assignedType, $assignedId, $key, $value, $sourceScope, $sourceId); }
    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void { $this->tagWriteRepository->clearEffectsForSource($tenantId, $sourceScope, $sourceId); }
    public function listAssignmentsByTag(string $tenantId, string $tagId): array { return $this->tagReadRepository->listAssignmentsByTag($tenantId, $tagId); }
    public function listTagsByScheme(string $tenantId, string $schemeName): array { return $this->tagReadRepository->listTagsByScheme($tenantId, $schemeName); }
}
