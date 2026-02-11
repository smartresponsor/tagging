<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;
use App\Domain\Tag\TagSynonym;

/**
 *
 */

/**
 *
 */
interface TagRepositoryInterface
{
    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\Tag $tag
     * @return void
     */
    public function saveTag(string $tenantId, Tag $tag): void;

    /**
     * @param string $tenantId
     * @param string $id
     * @return \App\Domain\Tag\Tag|null
     */
    public function getById(string $tenantId, string $id): ?Tag;

    /**
     * @param string $tenantId
     * @param string $slug
     * @return \App\Domain\Tag\Tag|null
     */
    public function getBySlug(string $tenantId, string $slug): ?Tag;

    /** @return Tag[] */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array;

    /**
     * @param string $tenantId
     * @param string $id
     * @return void
     */
    public function deleteTag(string $tenantId, string $id): void;

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagAssignment $a
     * @return void
     */
    public function saveAssignment(string $tenantId, TagAssignment $a): void;

    /**
     * @param string $tenantId
     * @param string $assignmentId
     * @return void
     */
    public function deleteAssignment(string $tenantId, string $assignmentId): void;

    /** @return TagAssignment[] */
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array;

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagSynonym $s
     * @return void
     */
    public function saveSynonym(string $tenantId, TagSynonym $s): void;

    /** @return TagSynonym[] */
    public function listSynonyms(string $tenantId, string $tagId): array;

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagRelation $r
     * @return void
     */
    public function saveRelation(string $tenantId, TagRelation $r): void;

    /** @return TagRelation[] */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array;

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagScheme $s
     * @return void
     */
    public function saveScheme(string $tenantId, TagScheme $s): void;

    /**
     * @param string $tenantId
     * @param string $name
     * @return \App\Domain\Tag\TagScheme|null
     */
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme;

    /**
     * @param string $tenantId
     * @param string $fromTagId
     * @param string $toTagId
     * @return void
     */
    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void;

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param bool $required
     * @param bool $modOnly
     * @return void
     */
    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void;

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $newLabel
     * @param string $newSlug
     * @return void
     */
    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void;

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $type
     * @param string $payloadJson
     * @return void
     */
    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void;

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $status
     * @param string|null $decidedBy
     * @return void
     */
    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void;

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $action
     * @param string $entityType
     * @param string $entityId
     * @param string $detailsJson
     * @return void
     */
    public function insertAudit(string $tenantId, string $id, string $action, string $entityType, string $entityId, string $detailsJson): void;

    /** @return Tag[] */
    public function listAllTags(string $tenantId): array;

    /**
     * @param string $tenantId
     * @return array
     */
    public function getPolicy(string $tenantId): array;

    /**
     * @param string $tenantId
     * @param array $policy
     * @return void
     */
    public function setPolicy(string $tenantId, array $policy): void;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function tagCloud(string $tenantId, int $limit): array;

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $scope
     * @param string $refId
     * @param string $key
     * @param string $value
     * @return void
     */
    public function putClassification(string $tenantId, string $id, string $scope, string $refId, string $key, string $value): void;

    /** @return array<int, array{key:string,value:string}> */
    public function listClassifications(string $tenantId, string $scope, string $refId): array;

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

    /**
     * @param string $tenantId
     * @param string $sourceScope
     * @param string $sourceId
     * @return void
     */
    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void;

    /** @return array<int, array{assigned_type:string,assigned_id:string}> */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array;

    /** @return array<int, array{tag_id:string}> */
    public function listTagsByScheme(string $tenantId, string $schemeName): array;
}
