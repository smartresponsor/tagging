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

interface TagWriteRepositoryInterface
{
    public function saveTag(string $vendorId, TagEntity $tag): void;

    public function deleteTag(string $vendorId, string $id): void;

    public function saveAssignment(string $vendorId, TagAssignmentEntity $a): void;

    public function deleteAssignment(string $vendorId, string $assignmentId): void;

    public function saveSynonym(string $vendorId, TagSynonymEntity $s): void;

    public function saveRelation(string $vendorId, TagRelationEntity $r): void;

    public function saveScheme(string $vendorId, TagSchemeEntity $s): void;

    public function reassignAssignments(string $vendorId, string $fromTagId, string $toTagId): void;

    public function renameTag(string $vendorId, string $tagId, string $newLabel, string $newSlug): void;

    public function insertProposal(string $vendorId, string $id, string $type, string $payloadJson): void;

    public function updateProposalStatus(string $vendorId, string $id, string $status, ?string $decidedBy): void;

    public function insertAudit(string $vendorId, TagAuditRecord $record): void;

    public function putClassification(string $vendorId, TagClassificationRecord $record): void;

    public function putEffect(string $vendorId, TagEffectRecord $record): void;

    public function clearEffectsForSource(string $vendorId, string $sourceScope, string $sourceId): void;
}
