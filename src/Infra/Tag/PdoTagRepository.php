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
use DateTimeImmutable;
use PDO;

/**
 *
 */

/**
 *
 */
final readonly class PdoTagRepository implements TagRepositoryInterface
{
    /**
     * @param \PDO $pdo
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\Tag $tag
     * @return void
     */
    public function saveTag(string $tenantId, Tag $tag): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO tag(tenant,id,slug,label,created_at) VALUES (:tenant,:id,:slug,:label,:created_at)
            ON CONFLICT (tenant,id) DO UPDATE SET slug=EXCLUDED.slug, label=EXCLUDED.label');
        $stmt->execute([':tenant' => $tenantId, ':id' => $tag->id(), ':slug' => $tag->slug(), ':label' => $tag->label(), ':created_at' => $tag->createdAt()->format('c')]);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @return \App\Domain\Tag\Tag|null
     * @throws \Exception
     */
    public function getById(string $tenantId, string $id): ?Tag
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tag WHERE tenant=:tenant AND id=:id');
        $stmt->execute([':tenant' => $tenantId, ':id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? new Tag($r['id'], $r['slug'], $r['label'], new DateTimeImmutable($r['created_at'])) : null;
    }

    /**
     * @param string $tenantId
     * @param string $slug
     * @return \App\Domain\Tag\Tag|null
     * @throws \Exception
     */
    public function getBySlug(string $tenantId, string $slug): ?Tag
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tag WHERE tenant=:tenant AND slug=:slug');
        $stmt->execute([':tenant' => $tenantId, ':slug' => $slug]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? new Tag($r['id'], $r['slug'], $r['label'], new DateTimeImmutable($r['created_at'])) : null;
    }

    /**
     * @param string $tenantId
     * @param string|null $query
     * @param int $limit
     * @param int $offset
     * @return array|\App\Domain\Tag\Tag[]
     * @throws \Exception
     */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array
    {
        $hasQuery = $query !== null && $query !== '';
        if ($hasQuery) {
            $stmt = $this->pdo->prepare('SELECT * FROM tag WHERE tenant=:tenant AND (slug ILIKE :q OR label ILIKE :q) ORDER BY created_at DESC LIMIT :l OFFSET :o');
            $stmt->bindValue(':q', '%' . $query . '%');
        } else {
            $stmt = $this->pdo->prepare('SELECT * FROM tag WHERE tenant=:tenant ORDER BY created_at DESC LIMIT :l OFFSET :o');
        }
        $stmt->bindValue(':tenant', $tenantId);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Tag($r['id'], $r['slug'], $r['label'], new DateTimeImmutable($r['created_at'])), $rows);
    }

    /**
     * @param string $tenantId
     * @param string $id
     * @return void
     */
    public function deleteTag(string $tenantId, string $id): void
    {
        $this->pdo->prepare('DELETE FROM tag WHERE tenant=:tenant AND id=:id')->execute([':tenant' => $tenantId, ':id' => $id]);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagAssignment $a
     * @return void
     */
    public function saveAssignment(string $tenantId, TagAssignment $a): void
    {
        $sql = 'INSERT INTO tag_assignment(tenant,id,tag_id,assigned_type,assigned_id,created_at) VALUES (:tenant,:id,:tag_id,:type,:aid,:created_at)
            ON CONFLICT (tenant,id) DO NOTHING';
        $this->pdo->prepare($sql)->execute([':tenant' => $tenantId, ':id' => $a->id(), ':tag_id' => $a->tagId(), ':type' => $a->assignedType(), ':aid' => $a->assignedId(), ':created_at' => $a->createdAt()->format('c')]);
    }

    /**
     * @param string $tenantId
     * @param string $assignmentId
     * @return void
     */
    public function deleteAssignment(string $tenantId, string $assignmentId): void
    {
        $this->pdo->prepare('DELETE FROM tag_assignment WHERE tenant=:tenant AND id=:id')->execute([':tenant' => $tenantId, ':id' => $assignmentId]);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string|null $type
     * @param string|null $assignedId
     * @return array|\App\Domain\Tag\TagAssignment[]
     * @throws \Exception
     */
    public function listAssignments(string $tenantId, string $tagId, ?string $type = null, ?string $assignedId = null): array
    {
        $sql = 'SELECT * FROM tag_assignment WHERE tenant=:tenant AND tag_id=:tid';
        $p = [':tenant' => $tenantId, ':tid' => $tagId];
        if ($type !== null) {
            $sql .= ' AND assigned_type=:t';
            $p[':t'] = $type;
        }
        if ($assignedId !== null) {
            $sql .= ' AND assigned_id=:aid';
            $p[':aid'] = $assignedId;
        }
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new TagAssignment($r['id'], $r['tag_id'], $r['assigned_type'], $r['assigned_id'], new DateTimeImmutable($r['created_at'])), $rows);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagSynonym $s
     * @return void
     */
    public function saveSynonym(string $tenantId, TagSynonym $s): void
    {
        $this->pdo->prepare('INSERT INTO tag_synonym(tenant,id,tag_id,label) VALUES (:tenant,:id,:tid,:label) ON CONFLICT (tenant,id) DO NOTHING')
            ->execute([':tenant' => $tenantId, ':id' => $s->id(), ':tid' => $s->tagId(), ':label' => $s->label()]);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array|\App\Domain\Tag\TagSynonym[]
     */
    public function listSynonyms(string $tenantId, string $tagId): array
    {
        $st = $this->pdo->prepare('SELECT * FROM tag_synonym WHERE tenant=:tenant AND tag_id=:tid');
        $st->execute([':tenant' => $tenantId, ':tid' => $tagId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new TagSynonym($r['id'], $r['tag_id'], $r['label']), $rows);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagRelation $r
     * @return void
     */
    public function saveRelation(string $tenantId, TagRelation $r): void
    {
        $this->pdo->prepare('INSERT INTO tag_relation(tenant,id,from_tag_id,to_tag_id,type) VALUES (:tenant,:id,:f,:t,:type) ON CONFLICT (tenant,id) DO NOTHING')
            ->execute([':tenant' => $tenantId, ':id' => $r->id(), ':f' => $r->fromTagId(), ':t' => $r->toTagId(), ':type' => $r->type()]);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @param string|null $type
     * @return array|\App\Domain\Tag\TagRelation[]
     */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array
    {
        $sql = 'SELECT * FROM tag_relation WHERE tenant=:tenant AND from_tag_id=:f';
        $p = [':tenant' => $tenantId, ':f' => $tagId];
        if ($type) {
            $sql .= ' AND type=:type';
            $p[':type'] = $type;
        }
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new TagRelation($r['id'], $r['from_tag_id'], $r['to_tag_id'], $r['type']), $rows);
    }

    /**
     * @param string $tenantId
     * @param \App\Domain\Tag\TagScheme $s
     * @return void
     */
    public function saveScheme(string $tenantId, TagScheme $s): void
    {
        $this->pdo->prepare('INSERT INTO tag_scheme(tenant,id,name,locale,created_at) VALUES (:tenant,:id,:name,:loc,:created_at)
            ON CONFLICT (tenant,id) DO UPDATE SET name=EXCLUDED.name, locale=EXCLUDED.locale')
            ->execute([':tenant' => $tenantId, ':id' => $s->id(), ':name' => $s->name(), ':loc' => $s->locale(), ':created_at' => $s->createdAt()->format('c')]);
    }

    /**
     * @param string $tenantId
     * @param string $name
     * @return \App\Domain\Tag\TagScheme|null
     * @throws \Exception
     */
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme
    {
        $st = $this->pdo->prepare('SELECT * FROM tag_scheme WHERE tenant=:tenant AND name=:n');
        $st->execute([':tenant' => $tenantId, ':n' => $name]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ? new TagScheme($r['id'], $r['name'], $r['locale'], new DateTimeImmutable($r['created_at'])) : null;
    }

    /**
     * @param string $tenantId
     * @param string $fromTagId
     * @param string $toTagId
     * @return void
     */
    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void
    {
        $this->pdo->prepare('UPDATE tag_assignment SET tag_id=:to WHERE tenant=:tenant AND tag_id=:from')->execute([':tenant' => $tenantId, ':to' => $toTagId, ':from' => $fromTagId]);
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
        $this->pdo->prepare('UPDATE tag SET required_flag=:r, mod_only_flag=:m WHERE tenant=:tenant AND id=:id')->execute([':tenant' => $tenantId, ':r' => $required ? 1 : 0, ':m' => $modOnly ? 1 : 0, ':id' => $tagId]);
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
        $this->pdo->prepare('UPDATE tag SET label=:l, slug=:s WHERE tenant=:tenant AND id=:id')->execute([':tenant' => $tenantId, ':l' => $newLabel, ':s' => $newSlug, ':id' => $tagId]);
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
        $this->pdo->prepare('INSERT INTO tag_proposal(tenant,id,type,payload,status) VALUES (:tenant,:id,:t,:p,\'pending\')')->execute([':tenant' => $tenantId, ':id' => $id, ':t' => $type, ':p' => $payloadJson]);
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
        $this->pdo->prepare('UPDATE tag_proposal SET status=:s, decided_at=now(), decided_by=:by WHERE tenant=:tenant AND id=:id')->execute([':tenant' => $tenantId, ':s' => $status, ':by' => $decidedBy, ':id' => $id]);
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
        $this->pdo->prepare('INSERT INTO tag_audit_log(tenant,id,action,entity_type,entity_id,details) VALUES (:tenant,:id,:a,:et,:eid,:d)')->execute([':tenant' => $tenantId, ':id' => $id, ':a' => $action, ':et' => $entityType, ':eid' => $entityId, ':d' => $detailsJson]);
    }

    /**
     * @param string $tenantId
     * @return array|\App\Domain\Tag\Tag[]
     * @throws \Exception
     */
    public function listAllTags(string $tenantId): array
    {
        $st = $this->pdo->prepare('SELECT * FROM tag WHERE tenant=:tenant');
        $st->execute([':tenant' => $tenantId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Tag($r['id'], $r['slug'], $r['label'], new DateTimeImmutable($r['created_at'])), $rows);
    }

    /**
     * @param string $tenantId
     * @return array
     * @throws \JsonException
     */
    public function getPolicy(string $tenantId): array
    {
        $st = $this->pdo->prepare('SELECT policy FROM tag_policy WHERE tenant=:tenant');
        $st->execute([':tenant' => $tenantId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? json_decode($row['policy'], true, 512, JSON_THROW_ON_ERROR) : [];
    }

    /**
     * @param string $tenantId
     * @param array $policy
     * @return void
     * @throws \JsonException
     */
    public function setPolicy(string $tenantId, array $policy): void
    {
        $st = $this->pdo->prepare('INSERT INTO tag_policy(tenant,policy) VALUES (:tenant,:p) ON CONFLICT (tenant) DO UPDATE SET policy=EXCLUDED.policy');
        $st->execute([':tenant' => $tenantId, ':p' => json_encode($policy, JSON_THROW_ON_ERROR)]);
    }

    /**
     * @param string $tenantId
     * @param string $assignedType
     * @param int $limit
     * @return array|array[]
     */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array
    {
        $sql = 'SELECT t.id as "tagId", t.slug, t.label, v.cnt::int as cnt FROM tag_stats_view v JOIN tag t ON t.tenant=v.tenant AND t.id=v.tag_id WHERE v.tenant=:tenant AND v.assigned_type=:t ORDER BY v.cnt DESC, t.slug ASC LIMIT :l';
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':tenant', $tenantId);
        $st->bindValue(':t', $assignedType);
        $st->bindValue(':l', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $tenantId
     * @param int $limit
     * @return array|array[]
     */
    public function tagCloud(string $tenantId, int $limit): array
    {
        $sql = 'SELECT t.id as "tagId", t.slug, t.label, SUM(v.cnt)::int as cnt FROM tag_stats_view v JOIN tag t ON t.tenant=v.tenant AND t.id=v.tag_id WHERE v.tenant=:tenant GROUP BY t.id, t.slug, t.label ORDER BY SUM(v.cnt) DESC LIMIT :l';
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':tenant', $tenantId);
        $st->bindValue(':l', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
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
        $this->pdo->prepare('INSERT INTO tag_classification(tenant,id,scope,ref_id,key,value) VALUES (:tenant,:id,:scope,:ref,:k,:v) ON CONFLICT (tenant,id) DO UPDATE SET key=EXCLUDED.key, value=EXCLUDED.value')->execute([':tenant' => $tenantId, ':id' => $id, ':scope' => $scope, ':ref' => $refId, ':k' => $key, ':v' => $value]);
    }

    /**
     * @param string $tenantId
     * @param string $scope
     * @param string $refId
     * @return array|array[]
     */
    public function listClassifications(string $tenantId, string $scope, string $refId): array
    {
        $st = $this->pdo->prepare('SELECT key,value FROM tag_classification WHERE tenant=:tenant AND scope=:s AND ref_id=:r');
        $st->execute([':tenant' => $tenantId, ':s' => $scope, ':r' => $refId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
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
        $this->pdo->prepare('INSERT INTO tag_assignment_effect(tenant,id,assigned_type,assigned_id,key,value,source_scope,source_id) VALUES (:tenant,:id,:t,:a,:k,:v,:ss,:sid) ON CONFLICT (tenant,id) DO NOTHING')->execute([':tenant' => $tenantId, ':id' => $id, ':t' => $assignedType, ':a' => $assignedId, ':k' => $key, ':v' => $value, ':ss' => $sourceScope, ':sid' => $sourceId]);
    }

    /**
     * @param string $tenantId
     * @param string $sourceScope
     * @param string $sourceId
     * @return void
     */
    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void
    {
        $this->pdo->prepare('DELETE FROM tag_assignment_effect WHERE tenant=:tenant AND source_scope=:s AND source_id=:id')->execute([':tenant' => $tenantId, ':s' => $sourceScope, ':id' => $sourceId]);
    }

    /**
     * @param string $tenantId
     * @param string $tagId
     * @return array|array[]
     */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array
    {
        $st = $this->pdo->prepare('SELECT assigned_type, assigned_id FROM tag_assignment WHERE tenant=:tenant AND tag_id=:t');
        $st->execute([':tenant' => $tenantId, ':t' => $tagId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $tenantId
     * @param string $schemeName
     * @return array|array[]
     */
    public function listTagsByScheme(string $tenantId, string $schemeName): array
    {
        $st = $this->pdo->prepare('SELECT t.id as tag_id FROM tag t JOIN tag_scheme s ON s.tenant=t.tenant WHERE t.tenant=:tenant AND s.name=:n');
        $st->execute([':tenant' => $tenantId, ':n' => $schemeName]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
