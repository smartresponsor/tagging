<?php
declare(strict_types=1);
namespace App\Infra\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface;
use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
final class InMemoryTagRepository implements TagRepositoryInterface {
    /** @var array<string,Tag> */
    private array $tags = [];
    /** @var array<string,TagAssignment> */
    private array $assignments = [];

    public function saveTag(Tag $tag): void { $this->tags[$tag->id()] = $tag; }
    public function getById(string $id): ?Tag { return $this->tags[$id] ?? null; }
    public function getBySlug(string $slug): ?Tag {
        foreach ($this->tags as $t) if ($t->slug() === $slug) return $t; return null;
    }
    public function search(?string $query, int $limit, int $offset): array {
        $all = array_values($this->tags);
        if ($query) {
            $q = mb_strtolower($query);
            $all = array_filter($all, fn($t) => str_contains(mb_strtolower($t->slug()), $q) || str_contains(mb_strtolower($t->label()), $q));
        }
        return array_slice(array_values($all), $offset, $limit);
    }
    public function deleteTag(string $id): void { unset($this->tags[$id]); }
    public function saveAssignment(TagAssignment $a): void { $this->assignments[$a->id()] = $a; }
    public function deleteAssignment(string $assignmentId): void { unset($this->assignments[$assignmentId]); }
    public function listAssignments(string $tagId, ?string $type = null, ?string $assignedId = null): array {
        return array_values(array_filter($this->assignments, function(TagAssignment $x) use($tagId,$type,$assignedId){
            if ($x->tagId() !== $tagId) return false;
            if ($type && $x->assignedType() !== $type) return false;
            if ($assignedId && $x->assignedId() !== $assignedId) return false;
            return true;
        }));
    }


public function reassignAssignments(string $fromTagId, string $toTagId): void {
    foreach ($this->assignments as $k=>$a) {
        if ($a->tagId() === $fromTagId) {
            $this->assignments[$k] = new TagAssignment($a->id(), $toTagId, $a->assignedType(), $a->assignedId(), $a->createdAt());
        }
    }
}
public function setTagFlags(string $tagId, bool $required, bool $modOnly): void { /* no-op for memory; flags not stored */ }
public function renameTag(string $tagId, string $newLabel, string $newSlug): void {
    if (!isset($this->tags[$tagId])) return;
    $t = $this->tags[$tagId];
    $this->tags[$tagId] = new \App\Domain\Tag\Tag($t->id(), $newSlug, $newLabel, $t->createdAt());
}
public function insertProposal(string $id, string $type, string $payloadJson): void { /* memory no-op */ }
public function updateProposalStatus(string $id, string $status, ?string $decidedBy): void { /* memory no-op */ }
public function insertAudit(string $id, string $action, string $entityType, string $entityId, string $detailsJson): void { /* memory no-op */ }


public function listAllTags(): array { return array_values($this->tags); }
private array $policy = [];
public function getPolicy(): array { return $this->policy; }
public function setPolicy(array $policy): void { $this->policy = $policy; }


public function facetTop(string $assignedType, int $limit): array {
    $cnt = [];
    foreach ($this->assignments as $a) {
        if ($a->assignedType() === $assignedType) {
            $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
        }
    }
    arsort($cnt);
    $out = [];
    foreach (array_slice(array_keys($cnt), 0, $limit) as $tagId) {
        $t = $this->tags[$tagId] ?? null; if (!$t) continue;
        $out[] = ["tagId"=>$tagId, "slug"=>$t->slug(), "label"=>$t->label(), "cnt"=>$cnt[$tagId]];
    }
    return $out;
}
public function tagCloud(int $limit): array {
    $cnt = [];
    foreach ($this->assignments as $a) {
        $cnt[$a->tagId()] = ($cnt[$a->tagId()] ?? 0) + 1;
    }
    arsort($cnt);
    $out = [];
    foreach (array_slice(array_keys($cnt), 0, $limit) as $tagId) {
        $t = $this->tags[$tagId] ?? null; if (!$t) continue;
        $out[] = ["tagId"=>$tagId, "slug"=>$t->slug(), "label"=>$t->label(), "cnt"=>$cnt[$tagId]];
    }
    return $out;
}


private array $class = []; // ["scope|ref" => [["key"=>...,"value"=>...], ...]]
private array $effects = []; // ["scope|id" => list of effects]
public function putClassification(string $id, string $scope, string $refId, string $key, string $value): void {
    $k = $scope.'|'.$refId;
    $this->class[$k] = $this->class[$k] ?? [];
    $this->class[$k][] = ["key"=>$key,"value"=>$value];
}
public function listClassifications(string $scope, string $refId): array {
    return $this->class[$scope.'|'.$refId] ?? [];
}
public function putEffect(string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void {
    $this->effects[$sourceScope.'|'.$sourceId] = $this->effects[$sourceScope.'|'.$sourceId] ?? [];
    $this->effects[$sourceScope.'|'.$sourceId][] = ["assigned_type"=>$assignedType,"assigned_id"=>$assignedId,"key"=>$key,"value"=>$value];
}
public function clearEffectsForSource(string $sourceScope, string $sourceId): void {
    unset($this->effects[$sourceScope.'|'.$sourceId]);
}
public function listAssignmentsByTag(string $tagId): array {
    $out = [];
    foreach ($this->assignments as $a) if ($a->tagId() === $tagId) $out[] = ["assigned_type"=>$a->assignedType(), "assigned_id"=>$a->assignedId()];
    return $out;
}
public function listTagsByScheme(string $schemeName): array {
    $out = [];
    foreach ($this->tags as $t) $out[] = ["tag_id"=>$t->id()];
    return $out;
}
}
