<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagEntityAssignment;
use App\Tagging\Entity\Tag\TagEntityRelation;
use App\Tagging\Entity\Tag\TagEntityScheme;
use App\Tagging\Entity\Tag\TagEntitySynonym;
use App\Tagging\Service\Core\Record\TagAuditRecord;
use App\Tagging\Service\Core\Record\TagClassificationRecord;
use App\Tagging\Service\Core\Record\TagEffectRecord;

interface TagWriteRepositoryInterface
{
    public function saveTag(string $tenantId, TagEntity $tag): void;

    public function deleteTag(string $tenantId, string $id): void;

    public function saveAssignment(string $tenantId, TagAssignmentEntity $a): void;

    public function deleteAssignment(string $tenantId, string $assignmentId): void;

    public function saveSynonym(string $tenantId, TagSynonymEntity $s): void;

    public function saveRelation(string $tenantId, TagRelationEntity $r): void;

    public function saveScheme(string $tenantId, TagSchemeEntity $s): void;

    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void;

    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void;

    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void;

    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void;

    public function insertAudit(string $tenantId, TagAuditRecord $record): void;

    public function putClassification(string $tenantId, TagClassificationRecord $record): void;

    public function putEffect(string $tenantId, TagEffectRecord $record): void;

    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void;
}
