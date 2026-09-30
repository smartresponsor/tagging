<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Normalizer\Core\TagNormalizer;
use App\Tagging\DTO\Write\TagAuditRecord;
use App\Tagging\RepositoryInterface\TagRepositoryInterface as TagRepositoryContract;
use Random\RandomException;

final readonly class TagModerationService
{
    public function __construct(private TagRepositoryContract $repo) {}

    /**
     * @throws \JsonException
     * @throws RandomException
     */
    public function propose(string $vendorId, string $type, array $payload): string
    {
        $id = TagUlidGenerator::generate();
        $this->repo->insertProposal($vendorId, $id, $type, json_encode($payload, JSON_THROW_ON_ERROR));
        $this->repo->insertAudit(
            $vendorId,
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

    public function approve(string $vendorId, string $id, string $decider): void
    {
        $this->repo->updateProposalStatus($vendorId, $id, 'approved', $decider);
    }

    /**
     * @throws \JsonException
     * @throws RandomException
     */
    public function mergeTags(string $vendorId, string $fromTagId, string $toTagId): void
    {
        $this->repo->reassignAssignments($vendorId, $fromTagId, $toTagId);
        $this->repo->deleteTag($vendorId, $fromTagId);
        $this->repo->insertAudit(
            $vendorId,
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
    public function renameTag(string $vendorId, string $tagId, string $newLabel): void
    {
        $slug = TagNormalizer::slugify($newLabel);
        $this->repo->renameTag($vendorId, $tagId, $newLabel, $slug);
        $this->repo->insertAudit(
            $vendorId,
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
    public function setFlags(string $vendorId, string $tagId, bool $required, bool $modOnly): void
    {
        $this->repo->setTagFlags($vendorId, $tagId, $required, $modOnly);
        $this->repo->insertAudit(
            $vendorId,
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
