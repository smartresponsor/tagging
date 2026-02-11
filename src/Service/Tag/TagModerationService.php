<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

/**
 *
 */

/**
 *
 */
final class TagModerationService
{
    /**
     * @param \App\ServiceInterface\Tag\TagRepositoryInterface $repo
     */
    public function __construct(private readonly TagRepositoryContract $repo)
    {
    }

    /**
     * @param string $tenantId
     * @param string $type
     * @param array $payload
     * @return string
     * @throws \JsonException
     * @throws \Random\RandomException
     */
    public function propose(string $tenantId, string $type, array $payload): string
    {
        $id = UlidGenerator::generate();
        $this->repo->insertProposal($tenantId, $id, $type, json_encode($payload, JSON_THROW_ON_ERROR));
        $this->repo->insertAudit($tenantId, UlidGenerator::generate(), 'proposal.create', 'proposal', $id, json_encode($payload, JSON_THROW_ON_ERROR));
        return $id;
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $decider
     * @return void
     */
    public function approve(string $tenantId, string $id, string $decider): void
    {
        $this->repo->updateProposalStatus($tenantId, $id, 'approved', $decider);
    }

    /**
     * @param string $tenantId
     * @param string $fromTagId
     * @param string $toTagId
     * @return void
     * @throws \JsonException
     * @throws \Random\RandomException
     */
    public function mergeTags(string $tenantId, string $fromTagId, string $toTagId): void
    {
        $this->repo->reassignAssignments($tenantId, $fromTagId, $toTagId);
        $this->repo->deleteTag($tenantId, $fromTagId);
        $this->repo->insertAudit($tenantId, UlidGenerator::generate(), 'tag.merge', 'tag', $toTagId, json_encode(['from' => $fromTagId, 'to' => $toTagId], JSON_THROW_ON_ERROR));
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $newLabel
     * @return void
     * @throws \JsonException
     * @throws \Random\RandomException
     */
    public function renameTag(string $tenantId, string $tagId, string $newLabel): void
    {
        $slug = TagNormalizer::slugify($newLabel);
        $this->repo->renameTag($tenantId, $tagId, $newLabel, $slug);
        $this->repo->insertAudit($tenantId, UlidGenerator::generate(), 'tag.rename', 'tag', $tagId, json_encode(['label' => $newLabel], JSON_THROW_ON_ERROR));
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param bool $required
     * @param bool $modOnly
     * @return void
     * @throws \JsonException
     * @throws \Random\RandomException
     */
    public function setFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void
    {
        $this->repo->setTagFlags($tenantId, $tagId, $required, $modOnly);
        $this->repo->insertAudit($tenantId, UlidGenerator::generate(), 'tag.flags', 'tag', $tagId, json_encode(['required' => $required, 'modOnly' => $modOnly], JSON_THROW_ON_ERROR));
    }
}
