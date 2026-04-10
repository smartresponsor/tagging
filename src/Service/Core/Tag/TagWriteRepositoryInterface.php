<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Core\Tag;

use App\Entity\Core\Tag\Tag;
use App\Entity\Core\Tag\TagAssignment;
use App\Entity\Core\Tag\TagRelation;
use App\Entity\Core\Tag\TagScheme;
use App\Entity\Core\Tag\TagSynonym;
use App\Service\Core\Tag\Record\TagAuditRecord;
use App\Service\Core\Tag\Record\TagClassificationRecord;
use App\Service\Core\Tag\Record\TagEffectRecord;

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

    public function insertAudit(string $tenantId, TagAuditRecord $record): void;

    public function putClassification(string $tenantId, TagClassificationRecord $record): void;

    public function putEffect(string $tenantId, TagEffectRecord $record): void;

    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void;
}
