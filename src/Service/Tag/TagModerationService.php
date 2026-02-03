<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;

final class TagModerationService {
    public function __construct(private TagRepositoryContract $repo){}

    public function propose(string $type, array $payload): string {
        $id = UlidGenerator::generate();
        $this->repo->insertProposal($id, $type, json_encode($payload, JSON_THROW_ON_ERROR));
        $this->repo->insertAudit(UlidGenerator::generate(), 'proposal.create', 'proposal', $id, json_encode($payload, JSON_THROW_ON_ERROR));
        return $id;
    }

    public function approve(string $id, string $decider): void {
        // In a real app you'd load proposal; here we trust caller to pass same payload again.
        $this->repo->updateProposalStatus($id, 'approved', $decider);
    }

    public function mergeTags(string $fromTagId, string $toTagId): void {
        $this->repo->reassignAssignments($fromTagId, $toTagId);
        // delete fromTag
        $this->repo->deleteTag($fromTagId);
        $this->repo->insertAudit(UlidGenerator::generate(), 'tag.merge', 'tag', $toTagId, json_encode(['from'=>$fromTagId,'to'=>$toTagId], JSON_THROW_ON_ERROR));
    }

    public function renameTag(string $tagId, string $newLabel): void {
        $slug = TagNormalizer::slugify($newLabel);
        $this->repo->renameTag($tagId, $newLabel, $slug);
        $this->repo->insertAudit(UlidGenerator::generate(), 'tag.rename', 'tag', $tagId, json_encode(['label'=>$newLabel], JSON_THROW_ON_ERROR));
    }

    public function setFlags(string $tagId, bool $required, bool $modOnly): void {
        $this->repo->setTagFlags($tagId, $required, $modOnly);
        $this->repo->insertAudit(UlidGenerator::generate(), 'tag.flags', 'tag', $tagId, json_encode(['required'=>$required,'modOnly'=>$modOnly], JSON_THROW_ON_ERROR));
    }
}
