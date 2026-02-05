<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Infra\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface;
use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;
use App\Domain\Tag\TagSynonym;

final class InMemoryTagRepository implements TagRepositoryInterface {
    /** @var array<string,array<string,Tag>> */
    private array $tags = [];
    /** @var array<string,array<string,TagAssignment>> */
    private array $assignments = [];
    /** @var array<string,array<string,mixed>> */
    private array $policy = [];
    private array $class = [];
    private array $effects = [];

    public function saveTag(string $tenantId, Tag $tag): void { $this->tags[$tenantId][$tag->id()] = $tag; }
    public function getById(string $tenantId, string $id): ?Tag { return $this->tags[$tenantId][$id] ?? null; }
    public function getBySlug(string $tenantId, string $slug): ?Tag {
        foreach (($this->tags[$tenantId] ?? []) as $t) if ($t->slug() === $slug) return $t; return null;
    }
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array {
        $all = array_values($this->tags[$tenantId] ?? []);
        if ($query) {
            $q = mb_strtolower($query);
            $all = array_filter($all, fn($t) => str_contains(mb_strtolower($t->slug()), $q) || str_contains(mb_strtolower($t->label()), $q));
        }
        return array_slice(array_values($all), $offset, $limit);
    }
    public function deleteTag(string $tenantId, string $id): void { unset($this->tags[$tenantId][$id]); }
    public function saveAssignment(string $tenantId, TagAssignment $a): void { $this->assignments[$tenantId][$a->id()] = $a; }
    public function deleteAssignment(string $tenantId, string $assignmentId): void { unset($this->assignments[$tenantId][$assignmentId]); }
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array {
        return array_values(array_filter($this->assignments[$tenantId] ?? [], function(TagAssignment $x) use($tagId,$type,$assignedId){
            if ($x->tagId() !== $tagId) return false;
            if ($type && $x->assignedType() !== $type) return false;
            if ($assignedId && $x->assignedId() !== $assignedId) return false;
            return true;
        }));
    }
    public function saveSynonym(string $tenantId, TagSynonym $s): void {}
    public function listSynonyms(string $tenantId, string $tagId): array { return []; }
    public function saveRelation(string $tenantId, TagRelation $r): void {}
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array { return []; }
    public function saveScheme(string $tenantId, TagScheme $s): void {}
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme { return null; }

    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void {
        foreach (($this->assignments[$tenantId] ?? []) as $k=>$a) {
            if ($a->tagId() === $fromTagId) {
                $this->assignments[$tenantId][$k] = new TagAssignment($a->id(), $toTagId, $a->assignedType(), $a->assignedId(), $a->createdAt());
            }
        }
    }
    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void {}
    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void {
        if (!isset($this->tags[$tenantId][$tagId])) return;
        $t = $this->tags[$tenantId][$tagId];
        $this->tags[$tenantId][$tagId] = new \App\Domain\Tag\Tag($t->id(), $newSlug, $newLabel, $t->createdAt());
    }
    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void {}
    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void {}
    public function insertAudit(string $tenantId, string $id, string $action, string $entityType, string $entityId, string $detailsJson): void {}
    public function listAllTags(string $tenantId): array { return array_values($this->tags[$tenantId] ?? []); }
    public function getPolicy(string $tenantId): array { return $this->policy[$tenantId] ?? []; }
    public function setPolicy(string $tenantId, array $policy): void { $this->policy[$tenantId] = $policy; }

    public function facetTop(string $tenantId, string $assignedType, int $limit): array {
        $cnt = [];
        foreach (($this->assignments[$tenantId] ?? []) as $a) {
            if ($a->assignedType() === $assignedType) {
                $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
            }
        }
        arsort($cnt);
        $out = [];
        foreach (array_slice(array_keys($cnt), 0, $limit) as $tagId) {
            $t = $this->tags[$tenantId][$tagId] ?? null; if (!$t) continue;
            $out[] = ["tagId"=>$tagId, "slug"=>$t->slug(), "label"=>$t->label(), "cnt"=>$cnt[$tagId]];
        }
        return $out;
    }
    public function tagCloud(string $tenantId, int $limit): array {
        $cnt = [];
        foreach (($this->assignments[$tenantId] ?? []) as $a) {
            $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
        }
        arsort($cnt);
        $out = [];
        foreach (array_slice(array_keys($cnt), 0, $limit) as $tagId) {
            $t = $this->tags[$tenantId][$tagId] ?? null; if (!$t) continue;
            $out[] = ["tagId"=>$tagId, "slug"=>$t->slug(), "label"=>$t->label(), "cnt"=>$cnt[$tagId]];
        }
        return $out;
    }

    public function putClassification(string $tenantId, string $id, string $scope, string $refId, string $key, string $value): void {
        $k = $tenantId.'|'.$scope.'|'.$refId;
        $this->class[$k] = $this->class[$k] ?? [];
        $this->class[$k][] = ["key"=>$key,"value"=>$value];
    }
    public function listClassifications(string $tenantId, string $scope, string $refId): array { return $this->class[$tenantId.'|'.$scope.'|'.$refId] ?? []; }
    public function putEffect(string $tenantId, string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void {
        $k = $tenantId.'|'.$sourceScope.'|'.$sourceId;
        $this->effects[$k] = $this->effects[$k] ?? [];
        $this->effects[$k][] = ["assigned_type"=>$assignedType,"assigned_id"=>$assignedId,"key"=>$key,"value"=>$value];
    }
    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void { unset($this->effects[$tenantId.'|'.$sourceScope.'|'.$sourceId]); }
    public function listAssignmentsByTag(string $tenantId, string $tagId): array {
        $out = [];
        foreach (($this->assignments[$tenantId] ?? []) as $a) if ($a->tagId() === $tagId) $out[] = ["assigned_type"=>$a->assignedType(), "assigned_id"=>$a->assignedId()];
        return $out;
    }
    public function listTagsByScheme(string $tenantId, string $schemeName): array {
        $out = [];
        foreach (($this->tags[$tenantId] ?? []) as $t) $out[] = ["tag_id"=>$t->id()];
        return $out;
    }
}
