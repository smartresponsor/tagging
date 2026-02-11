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
     * @param \App\Domain\Tag\Tag $tag
     * @return void
     */
    public function saveTag(Tag $tag): void;

    /**
     * @param string $id
     * @return \App\Domain\Tag\Tag|null
     */
    public function getById(string $id): ?Tag;

    /**
     * @param string $slug
     * @return \App\Domain\Tag\Tag|null
     */
    public function getBySlug(string $slug): ?Tag;

    /** @return Tag[] */
    public function search(?string $query, int $limit, int $offset): array;

    /**
     * @param string $id
     * @return void
     */
    public function deleteTag(string $id): void;

    /**
     * @param \App\Domain\Tag\TagAssignment $a
     * @return void
     */
    public function saveAssignment(TagAssignment $a): void;

    /**
     * @param string $assignmentId
     * @return void
     */
    public function deleteAssignment(string $assignmentId): void;

    /** @return TagAssignment[] */
    public function listAssignments(string $tagId, ?string $type = null, ?string $assignedId = null): array;

    // E3 additions

    /**
     * @param \App\Domain\Tag\TagSynonym $s
     * @return void
     */
    public function saveSynonym(TagSynonym $s): void;

    /** @return TagSynonym[] */
    public function listSynonyms(string $tagId): array;

    /**
     * @param \App\Domain\Tag\TagRelation $r
     * @return void
     */
    public function saveRelation(TagRelation $r): void;

    /** @return TagRelation[] */
    public function listRelations(string $tagId, ?string $type = null): array;

    /**
     * @param \App\Domain\Tag\TagScheme $s
     * @return void
     */
    public function saveScheme(TagScheme $s): void;

    /**
     * @param string $name
     * @return \App\Domain\Tag\TagScheme|null
     */
    public function getSchemeByName(string $name): ?TagScheme;

    // E4 additions

    /**
     * @param string $fromTagId
     * @param string $toTagId
     * @return void
     */
    public function reassignAssignments(string $fromTagId, string $toTagId): void;

    /**
     * @param string $tagId
     * @param bool $required
     * @param bool $modOnly
     * @return void
     */
    public function setTagFlags(string $tagId, bool $required, bool $modOnly): void;

    /**
     * @param string $tagId
     * @param string $newLabel
     * @param string $newSlug
     * @return void
     */
    public function renameTag(string $tagId, string $newLabel, string $newSlug): void;

    /**
     * @param string $id
     * @param string $type
     * @param string $payloadJson
     * @return void
     */
    public function insertProposal(string $id, string $type, string $payloadJson): void;

    /**
     * @param string $id
     * @param string $status
     * @param string|null $decidedBy
     * @return void
     */
    public function updateProposalStatus(string $id, string $status, ?string $decidedBy): void;

    /**
     * @param string $id
     * @param string $action
     * @param string $entityType
     * @param string $entityId
     * @param string $detailsJson
     * @return void
     */
    public function insertAudit(string $id, string $action, string $entityType, string $entityId, string $detailsJson): void;

    // E5 additions

    /** @return \App\Domain\Tag\Tag[] */
    public function listAllTags(): array;

    /**
     * @return array
     */
    public function getPolicy(): array;

    /**
     * @param array $policy
     * @return void
     */
    public function setPolicy(array $policy): void;

    // E6 additions

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function facetTop(string $assignedType, int $limit): array;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function tagCloud(int $limit): array;

    // E7 additions

    /**
     * @param string $id
     * @param string $scope
     * @param string $refId
     * @param string $key
     * @param string $value
     * @return void
     */
    public function putClassification(string $id, string $scope, string $refId, string $key, string $value): void;

    /** @return array<int, array{key:string,value:string}> */
    public function listClassifications(string $scope, string $refId): array;

    /**
     * @param string $id
     * @param string $assignedType
     * @param string $assignedId
     * @param string $key
     * @param string $value
     * @param string $sourceScope
     * @param string $sourceId
     * @return void
     */
    public function putEffect(string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void;

    /**
     * @param string $sourceScope
     * @param string $sourceId
     * @return void
     */
    public function clearEffectsForSource(string $sourceScope, string $sourceId): void;

    /** @return array<int, array{assigned_type:string,assigned_id:string}> */
    public function listAssignmentsByTag(string $tagId): array;

    /** @return array<int, array{tag_id:string}> */
    public function listTagsByScheme(string $schemeName): array;
}
