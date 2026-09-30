<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\RepositoryInterface;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Entity\Tag\TagSchemeEntity;
use App\Tagging\Entity\Tag\TagSynonymEntity;
use App\Tagging\DTO\Write\TagAuditRecord;
use App\Tagging\DTO\Write\TagClassificationRecord;
use App\Tagging\DTO\Write\TagEffectRecord;

interface TagRepositoryInterface
{
    public function saveTag(string $vendorId, TagEntity $tag): void;

    public function getById(string $vendorId, string $id): ?TagEntity;

    public function getBySlug(string $vendorId, string $slug): ?TagEntity;

    public function existsSlug(string $vendorId, string $slug, ?string $excludeTagId = null): bool;

    public function i18nSlugExists(string $vendorId, string $locale, string $slug, ?string $excludeTagId = null): bool;

    /** @return Tag[] */
    public function search(string $vendorId, ?string $query, int $limit, int $offset): array;

    public function deleteTag(string $vendorId, string $id): void;

    public function saveAssignment(string $vendorId, TagAssignmentEntity $a): void;

    public function deleteAssignment(string $vendorId, string $assignmentId): void;

    /** @return TagAssignment[] */
    public function listAssignments(
        string $vendorId,
        string $tagId,
        ?string $type = null,
        ?string $assignedId = null,
    ): array;

    public function saveSynonym(string $vendorId, TagSynonymEntity $s): void;

    /** @return TagSynonym[] */
    public function listSynonyms(string $vendorId, string $tagId): array;

    public function saveRelation(string $vendorId, TagRelationEntity $r): void;

    /** @return TagRelation[] */
    public function listRelations(string $vendorId, string $tagId, ?string $type = null): array;

    public function saveScheme(string $vendorId, TagSchemeEntity $s): void;

    public function getSchemeByName(string $vendorId, string $nameEntity): ?TagSchemeEntity;

    public function reassignAssignments(string $vendorId, string $fromTagId, string $toTagId): void;

    public function setTagFlags(string $vendorId, string $tagId, bool $required, bool $modOnly): void;

    public function renameTag(string $vendorId, string $tagId, string $newLabel, string $newSlug): void;

    public function insertProposal(string $vendorId, string $id, string $type, string $payloadJson): void;

    public function updateProposalStatus(string $vendorId, string $id, string $status, ?string $decidedBy): void;

    public function insertAudit(string $vendorId, TagAuditRecord $record): void;

    /** @return Tag[] */
    public function listAllTags(string $vendorId): array;

    public function countTags(string $vendorId): int;

    public function countAssignments(string $vendorId): int;

    public function getPolicy(string $vendorId): array;

    public function setPolicy(string $vendorId, array $policy): void;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function facetTop(string $vendorId, string $assignedType, int $limit): array;

    /** @return array<int, array{tagId:string, slug:string, label:string, cnt:int}> */
    public function tagCloud(string $vendorId, int $limit): array;

    public function putClassification(string $vendorId, TagClassificationRecord $record): void;

    /** @return array<int, array{key:string,value:string}> */
    public function listClassifications(string $vendorId, string $scope, string $refId): array;

    public function putEffect(string $vendorId, TagEffectRecord $record): void;

    public function clearEffectsForSource(string $vendorId, string $sourceScope, string $sourceId): void;

    /** @return array<int, array{assigned_type:string,assigned_id:string}> */
    public function listAssignmentsByTag(string $vendorId, string $tagId): array;

    /** @return array<int, array{tag_id:string}> */
    public function listTagsByScheme(string $vendorId, string $schemeName): array;
}
