<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Repository\Storage;

use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Entity\Tag\TagSchemeEntity;
use App\Tagging\Entity\Tag\TagSynonymEntity;
use App\Tagging\DTO\Write\TagAuditRecord;
use App\Tagging\DTO\Write\TagClassificationRecord;
use App\Tagging\DTO\Write\TagEffectRecord;
use App\Tagging\RepositoryInterface\TagRepositoryInterface;

final class TagInMemoryRepository implements TagRepositoryInterface
{
    /** @var array<string,array<string,Tag>> */
    private array $tags = [];
    /** @var array<string,array<string,TagAssignment>> */
    private array $assignments = [];
    /** @var array<string,array<string,mixed>> */
    private array $policy = [];
    private array $class = [];
    private array $effects = [];

    public function saveTag(string $vendorId, TagEntity $tag): void
    {
        $this->tags[$vendorId][$tag->id()] = $tag;
    }

    public function getById(string $vendorId, string $id): ?TagEntity
    {
        return $this->tags[$vendorId][$id] ?? null;
    }

    public function getBySlug(string $vendorId, string $slug): ?TagEntity
    {
        return array_find($this->tags[$vendorId] ?? [], fn($t) => $t->slug() === $slug);
    }

    public function existsSlug(string $vendorId, string $slug, ?string $excludeTagId = null): bool
    {
        foreach (($this->tags[$vendorId] ?? []) as $tag) {
            if (null !== $excludeTagId && $tag->id() === $excludeTagId) {
                continue;
            }
            if ($tag->slug() === $slug) {
                return true;
            }
        }

        return false;
    }

    public function i18nSlugExists(string $vendorId, string $locale, string $slug, ?string $excludeTagId = null): bool
    {
        return $this->existsSlug($vendorId, $slug, $excludeTagId);
    }

    /**
     * @return array|Tag[]
     */
    public function search(string $vendorId, ?string $query, int $limit, int $offset): array
    {
        $all = array_values($this->tags[$vendorId] ?? []);
        if ($query) {
            $q = mb_strtolower($query);
            $all = array_filter(
                $all,
                fn($t) => str_contains(mb_strtolower($t->slug()), $q)
                    || str_contains(mb_strtolower($t->label()), $q),
            );
        }

        return array_slice(array_values($all), $offset, $limit);
    }

    public function deleteTag(string $vendorId, string $id): void
    {
        unset($this->tags[$vendorId][$id]);
    }

    public function saveAssignment(string $vendorId, TagAssignmentEntity $a): void
    {
        $this->assignments[$vendorId][$a->id()] = $a;
    }

    public function deleteAssignment(string $vendorId, string $assignmentId): void
    {
        unset($this->assignments[$vendorId][$assignmentId]);
    }

    /**
     * @return array|TagAssignment[]
     */
    public function listAssignments(
        string $vendorId,
        string $tagId,
        ?string $type = null,
        ?string $assignedId = null,
    ): array {
        return array_values(array_filter(
            $this->assignments[$vendorId] ?? [],
            function (TagAssignmentEntity $x) use ($tagId, $type, $assignedId) {
                if ($x->tagId() !== $tagId) {
                    return false;
                }
                if ($type && $x->assignedType() !== $type) {
                    return false;
                }
                if ($assignedId && $x->assignedId() !== $assignedId) {
                    return false;
                }

                return true;
            },
        ));
    }

    public function saveSynonym(string $vendorId, TagSynonymEntity $s): void {}

    /**
     * @return array|TagSynonym[]
     */
    public function listSynonyms(string $vendorId, string $tagId): array
    {
        return [];
    }

    public function saveRelation(string $vendorId, TagRelationEntity $r): void {}

    /**
     * @return array|TagRelation[]
     */
    public function listRelations(string $vendorId, string $tagId, ?string $type = null): array
    {
        return [];
    }

    public function saveScheme(string $vendorId, TagSchemeEntity $s): void {}

    public function getSchemeByName(string $vendorId, string $nameEntity): ?TagSchemeEntity
    {
        return null;
    }

    public function reassignAssignments(string $vendorId, string $fromTagId, string $toTagId): void
    {
        foreach (($this->assignments[$vendorId] ?? []) as $k => $a) {
            if ($a->tagId() === $fromTagId) {
                $this->assignments[$vendorId][$k] = new TagAssignmentEntity(
                    $a->id(),
                    $vendorId,
                    $toTagId,
                    $a->assignedType(),
                    $a->assignedId(),
                    $a->createdAt(),
                );
            }
        }
    }

    public function setTagFlags(string $vendorId, string $tagId, bool $required, bool $modOnly): void {}

    public function renameTag(string $vendorId, string $tagId, string $newLabel, string $newSlug): void
    {
        if (!isset($this->tags[$vendorId][$tagId])) {
            return;
        }
        $t = $this->tags[$vendorId][$tagId];
        $this->tags[$vendorId][$tagId] = new TagEntity($t->id(), $vendorId, $newSlug, $newLabel, $t->createdAt());
    }

    public function insertProposal(string $vendorId, string $id, string $type, string $payloadJson): void {}

    public function updateProposalStatus(string $vendorId, string $id, string $status, ?string $decidedBy): void {}

    public function insertAudit(string $vendorId, TagAuditRecord $record): void {}

    /**
     * @return array|Tag[]
     */
    public function listAllTags(string $vendorId): array
    {
        return array_values($this->tags[$vendorId] ?? []);
    }

    public function countTags(string $vendorId): int
    {
        return count($this->tags[$vendorId] ?? []);
    }

    public function countAssignments(string $vendorId): int
    {
        return count($this->assignments[$vendorId] ?? []);
    }

    public function getPolicy(string $vendorId): array
    {
        return $this->policy[$vendorId] ?? [];
    }

    public function setPolicy(string $vendorId, array $policy): void
    {
        $this->policy[$vendorId] = $policy;
    }

    /**
     * @return array|array[]
     */
    public function facetTop(string $vendorId, string $assignedType, int $limit): array
    {
        $cnt = [];
        foreach (($this->assignments[$vendorId] ?? []) as $a) {
            if ($a->assignedType() === $assignedType) {
                $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
            }
        }

        return $this->topTagEntries($vendorId, $cnt, $limit);
    }

    /**
     * @return array|array[]
     */
    public function tagCloud(string $vendorId, int $limit): array
    {
        $cnt = [];
        foreach (($this->assignments[$vendorId] ?? []) as $a) {
            $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
        }

        return $this->topTagEntries($vendorId, $cnt, $limit);
    }

    /**
     * @param array<string,int> $counts
     *
     * @return array<int,array<string,int|string>>
     */
    private function topTagEntries(string $vendorId, array $counts, int $limit): array
    {
        arsort($counts);
        $out = [];
        foreach (array_slice(array_keys($counts), 0, $limit) as $tagId) {
            $tag = $this->tags[$vendorId][$tagId] ?? null;
            if (!$tag) {
                continue;
            }
            $out[] = ['tagId' => $tagId, 'slug' => $tag->slug(), 'label' => $tag->label(), 'cnt' => $counts[$tagId]];
        }

        return $out;
    }

    public function putClassification(string $vendorId, TagClassificationRecord $record): void
    {
        $k = $vendorId . '|' . $record->scope . '|' . $record->refId;
        $this->class[$k] = $this->class[$k] ?? [];
        $this->class[$k][] = ['key' => $record->key, 'value' => $record->value];
    }

    /**
     * @return array|array[]
     */
    public function listClassifications(string $vendorId, string $scope, string $refId): array
    {
        return $this->class[$vendorId . '|' . $scope . '|' . $refId] ?? [];
    }

    public function putEffect(string $vendorId, TagEffectRecord $record): void
    {
        $k = $vendorId . '|' . $record->sourceScope . '|' . $record->sourceId;
        $this->effects[$k] = $this->effects[$k] ?? [];
        $this->effects[$k][] = [
            'assigned_type' => $record->assignedType,
            'assigned_id' => $record->assignedId,
            'key' => $record->key,
            'value' => $record->value,
        ];
    }

    public function clearEffectsForSource(string $vendorId, string $sourceScope, string $sourceId): void
    {
        unset($this->effects[$vendorId . '|' . $sourceScope . '|' . $sourceId]);
    }

    /**
     * @return array|array[]
     */
    public function listAssignmentsByTag(string $vendorId, string $tagId): array
    {
        $out = [];
        foreach (($this->assignments[$vendorId] ?? []) as $a) {
            if ($a->tagId() === $tagId) {
                $out[] = ['assigned_type' => $a->assignedType(), 'assigned_id' => $a->assignedId()];
            }
        }

        return $out;
    }

    /**
     * @return array|array[]
     */
    public function listTagsByScheme(string $vendorId, string $schemeName): array
    {
        $out = [];
        foreach (($this->tags[$vendorId] ?? []) as $t) {
            $out[] = ['tag_id' => $t->id()];
        }

        return $out;
    }
}
