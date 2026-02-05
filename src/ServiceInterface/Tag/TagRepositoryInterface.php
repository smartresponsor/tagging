<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;
use App\Domain\Tag\TagSynonym;

interface TagRepositoryInterface
{
    public function saveTag(string $tenantId, Tag $tag): void;

    public function getById(string $tenantId, string $id): ?Tag;

    public function getBySlug(string $tenantId, string $slug): ?Tag;

    /** @return Tag[] */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array;

    public function deleteTag(string $tenantId, string $id): void;

    public function saveAssignment(string $tenantId, TagAssignment $a): void;

    public function deleteAssignment(string $tenantId, string $assignmentId): void;

    /** @return TagAssignment[] */
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array;

    public function saveSynonym(string $tenantId, TagSynonym $s): void;

    /** @return TagSynonym[] */
    public function listSynonyms(string $tenantId, string $tagId): array;

    public function saveRelation(string $tenantId, TagRelation $r): void;

    /** @return TagRelation[] */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array;

    public function saveScheme(string $tenantId, TagScheme $s): void;

    public function getSchemeByName(string $tenantId, string $name): ?TagScheme;

    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void;

    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void;

    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void;

    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void;

    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void;

    public function insertAudit(string $tenantId, string $id, string $action, string $entityType, string $entityId, string $detailsJson): void;

    /** @return Tag[] */
    public function listAllTags(string $tenantId): array;

    public function getPolicy(string $tenantId): array;

    public function setPolicy(string $tenantId, array $policy): void;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function tagCloud(string $tenantId, int $limit): array;

    public function putClassification(string $tenantId, string $id, string $scope, string $refId, string $key, string $value): void;

    /** @return array<int, array{key:string,value:string}> */
    public function listClassifications(string $tenantId, string $scope, string $refId): array;

    public function putEffect(
        string $tenantId,
        string $id,
        string $assignedType,
        string $assignedId,
        string $key,
        string $value,
        string $sourceScope,
        string $sourceId
    ): void;

    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void;

    /** @return array<int, array{assigned_type:string,assigned_id:string}> */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array;

    /** @return array<int, array{tag_id:string}> */
    public function listTagsByScheme(string $tenantId, string $schemeName): array;
}
