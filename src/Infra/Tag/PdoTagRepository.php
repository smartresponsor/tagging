<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Infra\Tag;

use PDO;
use App\ServiceInterface\Tag\TagRepositoryInterface;
use App\Domain\Tag\Tag;
use App\Domain\Tag\TagAssignment;
use App\Domain\Tag\TagSynonym;
use App\Domain\Tag\TagRelation;
use App\Domain\Tag\TagScheme;

final class PdoTagRepository implements TagRepositoryInterface {
    public function __construct(private PDO $pdo){}

    public function saveTag(Tag $tag): void {
        $stmt = $this->pdo->prepare('INSERT INTO tag(id, slug, label, created_at) VALUES (:id,:slug,:label,:created_at)
            ON CONFLICT (id) DO UPDATE SET slug=EXCLUDED.slug, label=EXCLUDED.label');
        $stmt->execute([':id'=>$tag->id(),':slug'=>$tag->slug(),':label'=>$tag->label(),':created_at'=>$tag->createdAt()->format('c')]);
    }
    public function getById(string $id): ?Tag {
        $stmt=$this->pdo->prepare('SELECT * FROM tag WHERE id=:id'); $stmt->execute([':id'=>$id]);
        $r=$stmt->fetch(PDO::FETCH_ASSOC); return $r? new Tag($r['id'],$r['slug'],$r['label'], new \DateTimeImmutable($r['created_at'])):null;
    }
    public function getBySlug(string $slug): ?Tag {
        $stmt=$this->pdo->prepare('SELECT * FROM tag WHERE slug=:slug'); $stmt->execute([':slug'=>$slug]);
        $r=$stmt->fetch(PDO::FETCH_ASSOC); return $r? new Tag($r['id'],$r['slug'],$r['label'], new \DateTimeImmutable($r['created_at'])):null;
    }
    public function search(?string $query, int $limit, int $offset): array {
        $hasQuery = $query !== null && $query !== '';
        if ($hasQuery) { $stmt=$this->pdo->prepare('SELECT * FROM tag WHERE slug ILIKE :q OR label ILIKE :q ORDER BY created_at DESC LIMIT :l OFFSET :o'); $stmt->bindValue(':q','%'.$query.'%'); }
        else { $stmt=$this->pdo->prepare('SELECT * FROM tag ORDER BY created_at DESC LIMIT :l OFFSET :o'); }
        $stmt->bindValue(':l',$limit,PDO::PARAM_INT); $stmt->bindValue(':o',$offset,PDO::PARAM_INT); $stmt->execute();
        $rows=$stmt->fetchAll(PDO::FETCH_ASSOC); return array_map(fn($r)=> new Tag($r['id'],$r['slug'],$r['label'], new \DateTimeImmutable($r['created_at'])), $rows);
    }
    public function deleteTag(string $id): void { $this->pdo->prepare('DELETE FROM tag WHERE id=:id')->execute([':id'=>$id]); }

    public function saveAssignment(TagAssignment $a): void {
        $sql='INSERT INTO tag_assignment(id, tag_id, assigned_type, assigned_id, created_at) VALUES (:id,:tag_id,:type,:aid,:created_at)
            ON CONFLICT (id) DO NOTHING';
        $this->pdo->prepare($sql)->execute([':id'=>$a->id(),':tag_id'=>$a->tagId(),':type'=>$a->assignedType(),':aid'=>$a->assignedId(),':created_at'=>$a->createdAt()->format('c')]);
    }
    public function deleteAssignment(string $assignmentId): void { $this->pdo->prepare('DELETE FROM tag_assignment WHERE id=:id')->execute([':id'=>$assignmentId]); }
    public function listAssignments(string $tagId, ?string $type = null, ?string $assignedId = null): array {
        $sql='SELECT * FROM tag_assignment WHERE tag_id=:tid'; $p=[':tid'=>$tagId];
        if ($type !== null) { $sql.=' AND assigned_type=:t'; $p[':t']=$type; }
        if ($assignedId !== null) { $sql.=' AND assigned_id=:aid'; $p[':aid']=$assignedId; }
        $st=$this->pdo->prepare($sql); $st->execute($p);
        $rows=$st->fetchAll(PDO::FETCH_ASSOC); return array_map(fn($r)=> new TagAssignment($r['id'],$r['tag_id'],$r['assigned_type'],$r['assigned_id'], new \DateTimeImmutable($r['created_at'])), $rows);
    }

    public function saveSynonym(TagSynonym $s): void {
        $this->pdo->prepare('INSERT INTO tag_synonym(id, tag_id, label) VALUES (:id,:tid,:label) ON CONFLICT (id) DO NOTHING')
            ->execute([':id'=>$s->id(),':tid'=>$s->tagId(),':label'=>$s->label()]);
    }
    public function listSynonyms(string $tagId): array {
        $st=$this->pdo->prepare('SELECT * FROM tag_synonym WHERE tag_id=:tid'); $st->execute([':tid'=>$tagId]);
        $rows=$st->fetchAll(PDO::FETCH_ASSOC); return array_map(fn($r)=> new TagSynonym($r['id'],$r['tag_id'],$r['label']), $rows);
    }

    public function saveRelation(TagRelation $r): void {
        $this->pdo->prepare('INSERT INTO tag_relation(id, from_tag_id, to_tag_id, type) VALUES (:id,:f,:t,:type) ON CONFLICT (id) DO NOTHING')
            ->execute([':id'=>$r->id(),':f'=>$r->fromTagId(),':t'=>$r->toTagId(),':type'=>$r->type()]);
    }
    public function listRelations(string $tagId, ?string $type = null): array {
        $sql='SELECT * FROM tag_relation WHERE from_tag_id=:f'; $p=[':f'=>$tagId];
        if ($type) { $sql.=' AND type=:type'; $p[':type']=$type; }
        $st=$this->pdo->prepare($sql); $st->execute($p);
        $rows=$st->fetchAll(PDO::FETCH_ASSOC); return array_map(fn($r)=> new TagRelation($r['id'],$r['from_tag_id'],$r['to_tag_id'],$r['type']), $rows);
    }

    public function saveScheme(TagScheme $s): void {
        $this->pdo->prepare('INSERT INTO tag_scheme(id, name, locale, created_at) VALUES (:id,:name,:loc,:created_at)
            ON CONFLICT (id) DO UPDATE SET name=EXCLUDED.name, locale=EXCLUDED.locale')
            ->execute([':id'=>$s->id(),':name'=>$s->name(),':loc'=>$s->locale(),':created_at'=>$s->createdAt()->format('c')]);
    }
    public function getSchemeByName(string $name): ?TagScheme {
        $st=$this->pdo->prepare('SELECT * FROM tag_scheme WHERE name=:n'); $st->execute([':n'=>$name]);
        $r=$st->fetch(PDO::FETCH_ASSOC); return $r? new TagScheme($r['id'],$r['name'],$r['locale'], new \DateTimeImmutable($r['created_at'])):null;
    }


public function reassignAssignments(string $fromTagId, string $toTagId): void {
    $this->pdo->beginTransaction();
    try {
        $stmt = $this->pdo->prepare('UPDATE tag_assignment SET tag_id=:to WHERE tag_id=:from');
        $stmt->execute([':to'=>$toTagId, ':from'=>$fromTagId]);
        $this->pdo->commit();
    } catch (\Throwable $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}
public function setTagFlags(string $tagId, bool $required, bool $modOnly): void {
    $this->pdo->prepare('UPDATE tag SET required_flag=:r, mod_only_flag=:m WHERE id=:id')
        ->execute([':r'=>$required?1:0, ':m'=>$modOnly?1:0, ':id'=>$tagId]);
}
public function renameTag(string $tagId, string $newLabel, string $newSlug): void {
    $sql='UPDATE tag SET label=:l, slug=:s WHERE id=:id';
    $this->pdo->prepare($sql)->execute([':l'=>$newLabel, ':s'=>$newSlug, ':id'=>$tagId]);
}
public function insertProposal(string $id, string $type, string $payloadJson): void {
    $this->pdo->prepare('INSERT INTO tag_proposal(id,type,payload,status) VALUES (:id,:t,:p,\'pending\')')
        ->execute([':id'=>$id, ':t'=>$type, ':p'=>$payloadJson]);
}
public function updateProposalStatus(string $id, string $status, ?string $decidedBy): void {
    $this->pdo->prepare('UPDATE tag_proposal SET status=:s, decided_at=now(), decided_by=:by WHERE id=:id')
        ->execute([':s'=>$status, ':by'=>$decidedBy, ':id'=>$id]);
}
public function insertAudit(string $id, string $action, string $entityType, string $entityId, string $detailsJson): void {
    $this->pdo->prepare('INSERT INTO tag_audit_log(id,action,entity_type,entity_id,details) VALUES (:id,:a,:et,:eid,:d)')
        ->execute([':id'=>$id, ':a'=>$action, ':et'=>$entityType, ':eid'=>$entityId, ':d'=>$detailsJson]);
}


public function listAllTags(): array {
    $st=$this->pdo->query('SELECT * FROM tag');
    $rows=$st->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($r)=> new Tag($r['id'],$r['slug'],$r['label'], new \DateTimeImmutable($r['created_at'])), $rows);
}
public function getPolicy(): array {
    $st=$this->pdo->query('SELECT policy FROM tag_policy WHERE id=1');
    $row=$st->fetch(PDO::FETCH_ASSOC);
    return $row ? json_decode($row['policy'], true, 512, JSON_THROW_ON_ERROR) : [];
}
public function setPolicy(array $policy): void {
    $st=$this->pdo->prepare('INSERT INTO tag_policy(id, policy) VALUES (1, :p) ON CONFLICT (id) DO UPDATE SET policy=EXCLUDED.policy');
    $st->execute([':p'=>json_encode($policy, JSON_THROW_ON_ERROR)]);
}


public function facetTop(string $assignedType, int $limit): array {
    $sql = 'SELECT t.id as "tagId", t.slug, t.label, v.cnt::int as cnt
            FROM tag_stats_view v JOIN tag t ON t.id=v.tag_id
            WHERE v.assigned_type=:t ORDER BY v.cnt DESC, t.slug ASC LIMIT :l';
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':t', $assignedType);
    $st->bindValue(':l', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
public function tagCloud(int $limit): array {
    $sql = 'SELECT t.id as "tagId", t.slug, t.label, SUM(v.cnt)::int as cnt
            FROM tag_stats_view v JOIN tag t ON t.id=v.tag_id
            GROUP BY t.id, t.slug, t.label ORDER BY SUM(v.cnt) DESC LIMIT :l';
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':l', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}


public function putClassification(string $id, string $scope, string $refId, string $key, string $value): void {
    $this->pdo->prepare('INSERT INTO tag_classification(id, scope, ref_id, key, value) VALUES (:id,:scope,:ref,:k,:v)
        ON CONFLICT (id) DO UPDATE SET key=EXCLUDED.key, value=EXCLUDED.value')->execute([':id'=>$id,':scope'=>$scope,':ref'=>$refId,':k'=>$key,':v'=>$value]);
}
public function listClassifications(string $scope, string $refId): array {
    $st=$this->pdo->prepare('SELECT key,value FROM tag_classification WHERE scope=:s AND ref_id=:r'); $st->execute([':s'=>$scope,':r'=>$refId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
public function putEffect(string $id, string $assignedType, string $assignedId, string $key, string $value, string $sourceScope, string $sourceId): void {
    $this->pdo->prepare('INSERT INTO tag_assignment_effect(id,assigned_type,assigned_id,key,value,source_scope,source_id) VALUES (:id,:t,:a,:k,:v,:ss,:sid)
        ON CONFLICT (id) DO NOTHING')->execute([':id'=>$id,':t'=>$assignedType,':a'=>$assignedId,':k'=>$key,':v'=>$value,':ss'=>$sourceScope,':sid'=>$sourceId]);
}
public function clearEffectsForSource(string $sourceScope, string $sourceId): void {
    $this->pdo->prepare('DELETE FROM tag_assignment_effect WHERE source_scope=:s AND source_id=:id')->execute([':s'=>$sourceScope,':id'=>$sourceId]);
}
public function listAssignmentsByTag(string $tagId): array {
    $st=$this->pdo->prepare('SELECT assigned_type, assigned_id FROM tag_assignment WHERE tag_id=:t'); $st->execute([':t'=>$tagId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
public function listTagsByScheme(string $schemeName): array {
    $st=$this->pdo->prepare('SELECT t.id as tag_id FROM tag t JOIN tag_scheme s ON s.name=:n'); $st->execute([':n'=>$schemeName]);
    // NOTE: simple example: all tags belong to scheme by name match rule in real life; here we return all tags for demo.
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
}
