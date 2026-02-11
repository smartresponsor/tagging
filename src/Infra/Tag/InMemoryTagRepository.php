<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Tag;

use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;
use App\Domain\Tag\TagSynonym;
use App\ServiceInterface\Tag\TagRepositoryInterface;

/**
 *
 */

/**
 *
 */
final class InMemoryTagRepository implements TagRepositoryInterface
{
    /** @var array<string,array<string,Tag>> */
    private array $tags = [];
    /** @var array<string,array<string,TagAssignment>> */
    private array $assignments = [];
    /** @var array<string,array<string,mixed>> */
    private array $policy = [];
    private array $class = [];
    private array $effects = [];

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\Tag $tag
     * @return void
     */
    public function saveTag(string $tenantId, Tag $tag): void
    {
        $this->tags[$tenantId][$tag->id()] = $tag;
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @return \App\Domain\Tag\Tag|null
     */
    public function getById(string $tenantId, string $id): ?Tag
    {
        return $this->tags[$tenantId][$id] ?? null;
    }

    /**
     * @param string $tenantId
     * @param string $slug
     * @return \App\Domain\Tag\Tag|null
     */
    public function getBySlug(string $tenantId, string $slug): ?Tag
    {
        foreach (($this->tags[$tenantId] ?? []) as $t) if ($t->slug() === $slug) return $t;
        return null;
    }

    /**
     * @param string $tenantId
     * @param string|null $query
     * @param int $limit
     * @param int $offset
     * @return array|\App\Domain\Tag\Tag[]
     */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array
    {
        $all = array_values($this->tags[$tenantId] ?? []);
        if ($query) {
            $q = mb_strtolower($query);
            $all = array_filter($all, fn($t) => str_contains(mb_strtolower($t->slug()), $q) || str_contains(mb_strtolower($t->label()), $q));
        }
        return array_slice(array_values($all), $offset, $limit);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @return void
     */
    public function deleteTag(string $tenantId, string $id): void
    {
        unset($this->tags[$tenantId][$id]);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagAssignment $a
     * @return void
     */
    public function saveAssignment(string $tenantId, TagAssignment $a): void
    {
        $this->assignments[$tenantId][$a->id()] = $a;
    }

    /**
     * @param string $tenantId
     * @param string $assignmentId
     * @return void
     */
    public function deleteAssignment(string $tenantId, string $assignmentId): void
    {
        unset($this->assignments[$tenantId][$assignmentId]);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string|null $type
     * @param string|null $assignedId
     * @return array|\App\Domain\Tag\TagAssignment[]
     */
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array
    {
        return array_values(array_filter($this->assignments[$tenantId] ?? [], function (TagAssignment $x) use ($tagId, $type, $assignedId) {
            if ($x->tagId() !== $tagId) return false;
            if ($type && $x->assignedType() !== $type) return false;
            if ($assignedId && $x->assignedId() !== $assignedId) return false;
            return true;
        }));
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagSynonym $s
     * @return void
     */
    public function saveSynonym(string $tenantId, TagSynonym $s): void
    {
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array|\App\Domain\Tag\TagSynonym[]
     */
    public function listSynonyms(string $tenantId, string $tagId): array
    {
        return [];
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagRelation $r
     * @return void
     */
    public function saveRelation(string $tenantId, TagRelation $r): void
    {
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string|null $type
     * @return array|\App\Domain\Tag\TagRelation[]
     */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array
    {
        return [];
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagScheme $s
     * @return void
     */
    public function saveScheme(string $tenantId, TagScheme $s): void
    {
    }

    /**
     * @param string $tenantId
     * @param string $name
     * @return \App\Domain\Tag\TagScheme|null
     */
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme
    {
        return null;
    }

    /**
     * @param string $tenantId
     * @param string $fromTagId
     * @param string $toTagId
     * @return void
     */
    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void
    {
        foreach (($this->assignments[$tenantId] ?? []) as $k => $a) {
            if ($a->tagId() === $fromTagId) {
                $this->assignments[$tenantId][$k] = new TagAssignment($a->id(), $toTagId, $a->assignedType(), $a->assignedId(), $a->createdAt());
            }
        }
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param bool $required
     * @param bool $modOnly
     * @return void
     */
    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void
    {
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string $newLabel
     * @param string $newSlug
     * @return void
     */
    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void
    {
        if (!isset($this->tags[$tenantId][$tagId])) return;
        $t = $this->tags[$tenantId][$tagId];
        $this->tags[$tenantId][$tagId] = new Tag($t->id(), $newSlug, $newLabel, $t->createdAt());
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $type
     * @param string $payloadJson
     * @return void
     */
    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void
    {
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $status
     * @param string|null $decidedBy
     * @return void
     */
    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void
    {
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $action
     * @param string $entityType
     * @param string $entityId
     * @param string $detailsJson
     * @return void
     */
    public function insertAudit(string $tenantId, string $id, string $action, string $entityType, string $entityId, string $detailsJson): void
    {
    }

    /**
     * @param string $tenantId
     * @return array|\App\Domain\Tag\Tag[]
     */
    public function listAllTags(string $tenantId): array
    {
        return array_values($this->tags[$tenantId] ?? []);
    }

    /**
     * @param string $tenantId
     * @return array|mixed[]
     */
    public function getPolicy(string $tenantId): array
    {
        return $this->policy[$tenantId] ?? [];
    }

    /**
     * @param string $tenantId
     * @param array $policy
     * @return void
     */
    public function setPolicy(string $tenantId, array $policy): void
    {
        $this->policy[$tenantId] = $policy;
    }

    /**
     * @param string $tenantId
     * @param string $assignedType
     * @param int $limit
     * @return array|array[]
     */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array
    {
        $cnt = [];
        foreach (($this->assignments[$tenantId] ?? []) as $a) {
            if ($a->assignedType() === $assignedType) {
                $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
            }
        }
        arsort($cnt);
        $out = [];
        foreach (array_slice(array_keys($cnt), 0, $limit) as $tagId) {
            $t = $this->tags[$tenantId][$tagId] ?? null;
            if (!$t) continue;
            $out[] = ['tagId' => $tagId, 'slug' => $t->slug(), 'label' => $t->label(), 'cnt' => $cnt[$tagId]];
        }
        return $out;
    }

    /**
     * @param string $tenantId
     * @param int $limit
     * @return array|array[]
     */
    public function tagCloud(string $tenantId, int $limit): array
    {
        $cnt = [];
        foreach (($this->assignments[$tenantId] ?? []) as $a) {
            $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
        }
        arsort($cnt);
        $out = [];
        foreach (array_slice(array_keys($cnt), 0, $limit) as $tagId) {
            $t = $this->tags[$tenantId][$tagId] ?? null;
            if (!$t) continue;
            $out[] = ['tagId' => $tagId, 'slug' => $t->slug(), 'label' => $t->label(), 'cnt' => $cnt[$tagId]];
        }
        return $out;
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @param string $scope
     * @param string $refId
     * @param string $key
     * @param string $value
     * @return void
     */
    public function putClassification(string $tenantId, string $id, string $scope, string $refId, string $key, string $value): void
    {
        $k = $tenantId . '|' . $scope . '|' . $refId;
        $this->class[$k] = $this->class[$k] ?? [];
        $this->class[$k][] = ['key' => $key, 'value' => $value];
    }

    /**
     * @param string $tenantId
     * @param string $scope
     * @param string $refId
     * @return array|array[]
     */
    public function listClassifications(string $tenantId, string $scope, string $refId): array
    {
        return $this->class[$tenantId . '|' . $scope . '|' . $refId] ?? [];
    }

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
    public function putEffect(string $tenantId, string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void
    {
        $k = $tenantId . '|' . $sourceScope . '|' . $sourceId;
        $this->effects[$k] = $this->effects[$k] ?? [];
        $this->effects[$k][] = ['assigned_type' => $assignedType, 'assigned_id' => $assignedId, 'key' => $key, 'value' => $value];
    }

    /**
     * @param string $tenantId
     * @param string $sourceScope
     * @param string $sourceId
     * @return void
     */
    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void
    {
        unset($this->effects[$tenantId . '|' . $sourceScope . '|' . $sourceId]);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array|array[]
     */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array
    {
        $out = [];
        foreach (($this->assignments[$tenantId] ?? []) as $a) if ($a->tagId() === $tagId) $out[] = ['assigned_type' => $a->assignedType(), 'assigned_id' => $a->assignedId()];
        return $out;
    }

    /**
     * @param string $tenantId
     * @param string $schemeName
     * @return array|array[]
     */
    public function listTagsByScheme(string $tenantId, string $schemeName): array
    {
        $out = [];
        foreach (($this->tags[$tenantId] ?? []) as $t) $out[] = ['tag_id' => $t->id()];
        return $out;
    }
}
