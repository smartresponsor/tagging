<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Service\Core\Record\TagAuditRecord;
use App\Tagging\Service\Core\TagRepositoryInterface as TagRepositoryContract;
use Random\RandomException;

final readonly class TagModerationService
{
    public function __construct(private TagRepositoryContract $repo) {}

    /**
     * @throws \JsonException
     * @throws RandomException
     */
    public function propose(string $tenantId, string $type, array $payload): string
    {
        $id = TagUlidGenerator::generate();
        $this->repo->insertProposal($tenantId, $id, $type, json_encode($payload, JSON_THROW_ON_ERROR));
        $this->repo->insertAudit(
            $tenantId,
            new TagAuditRecord(
                TagUlidGenerator::generate(),
                'proposal.create',
                'proposal',
                $id,
                json_encode($payload, JSON_THROW_ON_ERROR),
            ),
        );

        return $id;
    }

    public function approve(string $tenantId, string $id, string $decider): void
    {
        $this->repo->updateProposalStatus($tenantId, $id, 'approved', $decider);
    }

    /**
     * @throws \JsonException
     * @throws RandomException
     */
    public function mergeTags(string $tenantId, string $fromTagId, string $toTagId): void
    {
        $this->repo->reassignAssignments($tenantId, $fromTagId, $toTagId);
        $this->repo->deleteTag($tenantId, $fromTagId);
        $this->repo->insertAudit(
            $tenantId,
            new TagAuditRecord(
                TagUlidGenerator::generate(),
                'tag.merge',
                'tag',
                $toTagId,
                json_encode([
                    'from' => $fromTagId,
                    'to' => $toTagId,
                ], JSON_THROW_ON_ERROR),
            ),
        );
    }

    /**
     * @throws \JsonException
     * @throws RandomException
     */
    public function renameTag(string $tenantId, string $tagId, string $newLabel): void
    {
        $slug = TagNormalizer::slugify($newLabel);
        $this->repo->renameTag($tenantId, $tagId, $newLabel, $slug);
        $this->repo->insertAudit(
            $tenantId,
            new TagAuditRecord(
                TagUlidGenerator::generate(),
                'tag.rename',
                'tag',
                $tagId,
                json_encode(['label' => $newLabel], JSON_THROW_ON_ERROR),
            ),
        );
    }

    /**
     * @throws \JsonException
     * @throws RandomException
     */
    public function setFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void
    {
        $this->repo->setTagFlags($tenantId, $tagId, $required, $modOnly);
        $this->repo->insertAudit(
            $tenantId,
            new TagAuditRecord(
                TagUlidGenerator::generate(),
                'tag.flags',
                'tag',
                $tagId,
                json_encode([
                    'required' => $required,
                    'modOnly' => $modOnly,
                ], JSON_THROW_ON_ERROR),
            ),
        );
    }
}
