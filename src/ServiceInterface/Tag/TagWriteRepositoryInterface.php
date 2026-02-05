<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Tag;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;
use App\Domain\Tag\TagSynonym;

interface TagWriteRepositoryInterface
{
    public function saveTag(string $tenantId, Tag $tag): void;

    public function deleteTag(string $tenantId, string $id): void;

    public function saveAssignment(string $tenantId, TagAssignment $a): void;

    public function deleteAssignment(string $tenantId, string $assignmentId): void;

    public function saveSynonym(string $tenantId, TagSynonym $s): void;

    public function saveRelation(string $tenantId, TagRelation $r): void;

    public function saveScheme(string $tenantId, TagScheme $s): void;

    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void;

    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void;

    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void;

    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void;

    public function insertAudit(string $tenantId, string $id, string $action, string $entityType, string $entityId, string $detailsJson): void;

    public function putClassification(string $tenantId, string $id, string $scope, string $refId, string $key, string $value): void;

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
}
