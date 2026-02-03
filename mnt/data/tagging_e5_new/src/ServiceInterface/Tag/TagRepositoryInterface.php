<?php
declare(strict_types=1);
namespace App\ServiceInterface\Tag;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagSynonym;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
interface TagRepositoryInterface {
    public function saveTag(Tag $tag): void;
    public function getById(string $id): ?Tag;
    public function getBySlug(string $slug): ?Tag;
    /** @return Tag[] */
    public function search(?string $query, int $limit, int $offset): array;
    public function deleteTag(string $id): void;

    public function saveAssignment(TagAssignment $a): void;
    public function deleteAssignment(string $assignmentId): void;
    /** @return TagAssignment[] */
    public function listAssignments(string $tagId, ?string $type = null, ?string $assignedId = null): array;

    // E3 additions
    public function saveSynonym(TagSynonym $s): void;
    /** @return TagSynonym[] */
    public function listSynonyms(string $tagId): array;

    public function saveRelation(TagRelation $r): void;
    /** @return TagRelation[] */
    public function listRelations(string $tagId, ?string $type = null): array;

    public function saveScheme(TagScheme $s): void;
    public function getSchemeByName(string $name): ?TagScheme;

    // E4 additions
    public function reassignAssignments(string $fromTagId, string $toTagId): void;
    public function setTagFlags(string $tagId, bool $required, bool $modOnly): void;
    public function renameTag(string $tagId, string $newLabel, string $newSlug): void;
    public function insertProposal(string $id, string $type, string $payloadJson): void;
    public function updateProposalStatus(string $id, string $status, ?string $decidedBy): void;
    public function insertAudit(string $id, string $action, string $entityType, string $entityId, string $detailsJson): void;

    // E5 additions
            /** @return \App\Domain\Tag\Tag[] */
    public function listAllTags(): array;
    public function getPolicy(): array;
    public function setPolicy(array $policy): void;

    // E6 additions
            /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function facetTop(string $assignedType, int $limit): array;
            /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function tagCloud(int $limit): array;

    // E7 additions
    public function putClassification(string $id, string $scope, string $refId, string $key, string $value): void;
            /** @return array<int, array{key:string,value:string}> */
    public function listClassifications(string $scope, string $refId): array;
    public function putEffect(string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void;
    public function clearEffectsForSource(string $sourceScope, string $sourceId): void;
            /** @return array<int, array{assigned_type:string,assigned_id:string}> */
    public function listAssignmentsByTag(string $tagId): array;
            /** @return array<int, array{tag_id:string}> */
    public function listTagsByScheme(string $schemeName): array;
}
