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
interface TagWriteRepositoryInterface
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

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagSynonym $s
     * @return void
     */
    public function saveSynonym(string $tenantId, TagSynonym $s): void;

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagRelation $r
     * @return void
     */
    public function saveRelation(string $tenantId, TagRelation $r): void;

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagScheme $s
     * @return void
     */
    public function saveScheme(string $tenantId, TagScheme $s): void;

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
}
