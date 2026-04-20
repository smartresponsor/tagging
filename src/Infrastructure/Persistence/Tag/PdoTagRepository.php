<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Infrastructure\Persistence\Tag;

use App\Tagging\Entity\Core\Tag\Tag;
use App\Tagging\Entity\Core\Tag\TagAssignment;
use App\Tagging\Entity\Core\Tag\TagRelation;
use App\Tagging\Entity\Core\Tag\TagScheme;
use App\Tagging\Entity\Core\Tag\TagSynonym;
use App\Tagging\Service\Core\Tag\Record\TagAuditRecord;
use App\Tagging\Service\Core\Tag\Record\TagClassificationRecord;
use App\Tagging\Service\Core\Tag\Record\TagEffectRecord;
use App\Tagging\Service\Core\Tag\TagRepositoryInterface;

final readonly class PdoTagRepository implements TagRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    /** @param array<string, mixed> $params */
    private function execute(string $sql, array $params = []): \PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /** @param array<string, mixed> $params */
    private function fetchAssoc(string $sql, array $params = []): array|false
    {
        return $this->execute($sql, $params)->fetch(\PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    private function fetchAllAssoc(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $row
     * @throws \Exception
     */
    private function hydrateTag(array $row): Tag
    {
        return new Tag($row['id'], $row['slug'], $row['label'], new \DateTimeImmutable($row['created_at']));
    }

    /**
     * @param array<string, mixed> $row
     *
     * @throws \DateMalformedStringException
     */
    private function hydrateAssignment(array $row): TagAssignment
    {
        return new TagAssignment(
            $row['id'],
            $row['tag_id'],
            $row['assigned_type'],
            $row['assigned_id'],
            new \DateTimeImmutable($row['created_at']),
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrateSynonym(array $row): TagSynonym
    {
        return new TagSynonym($row['id'], $row['tag_id'], $row['label']);
    }

    /** @param array<string, mixed> $row */
    private function hydrateRelation(array $row): TagRelation
    {
        return new TagRelation($row['id'], $row['from_tag_id'], $row['to_tag_id'], $row['type']);
    }

    /** @param array<string, mixed> $row
     * @throws \Exception
     */
    private function hydrateScheme(array $row): TagScheme
    {
        return new TagScheme($row['id'], $row['name'], $row['locale'], new \DateTimeImmutable($row['created_at']));
    }

    public function saveTag(string $tenantId, Tag $tag): void
    {
        $this->execute(
            'INSERT INTO tag(tenant,id,slug,label,created_at) VALUES (:tenant,:id,:slug,:label,:created_at)
            ON CONFLICT (tenant,id) DO UPDATE SET slug=EXCLUDED.slug, label=EXCLUDED.label',
            [
                ':tenant' => $tenantId,
                ':id' => $tag->id(),
                ':slug' => $tag->slug(),
                ':label' => $tag->label(),
                ':created_at' => $tag->createdAt()->format('c'),
            ],
        );
    }

    /**
     * @throws \Exception
     */
    public function getById(string $tenantId, string $id): ?Tag
    {
        $r = $this->fetchAssoc(
            'SELECT * FROM tag WHERE tenant=:tenant AND id=:id',
            [':tenant' => $tenantId, ':id' => $id],
        );

        return is_array($r) ? $this->hydrateTag($r) : null;
    }

    /**
     * @throws \Exception
     */
    public function getBySlug(string $tenantId, string $slug): ?Tag
    {
        $r = $this->fetchAssoc(
            'SELECT * FROM tag WHERE tenant=:tenant AND slug=:slug',
            [':tenant' => $tenantId, ':slug' => $slug],
        );

        return is_array($r) ? $this->hydrateTag($r) : null;
    }

    public function existsSlug(string $tenantId, string $slug, ?string $excludeTagId = null): bool
    {
        $sql = 'SELECT 1 FROM tag WHERE tenant=:tenant AND slug=:slug';
        $params = [':tenant' => $tenantId, ':slug' => $slug];

        if (null !== $excludeTagId && '' !== $excludeTagId) {
            $sql .= ' AND id <> :exclude';
            $params[':exclude'] = $excludeTagId;
        }

        $stmt = $this->execute($sql . ' LIMIT 1', $params);

        return false !== $stmt->fetchColumn();
    }

    public function i18nSlugExists(string $tenantId, string $locale, string $slug, ?string $excludeTagId = null): bool
    {
        $sql = 'SELECT 1 FROM tag_entity WHERE tenant=:tenant AND locale=:locale AND slug=:slug';
        $params = [':tenant' => $tenantId, ':locale' => $locale, ':slug' => $slug];

        if (null !== $excludeTagId && '' !== $excludeTagId) {
            $sql .= ' AND id <> :exclude';
            $params[':exclude'] = $excludeTagId;
        }

        $stmt = $this->execute($sql . ' LIMIT 1', $params);

        return false !== $stmt->fetchColumn();
    }

    /**
     * @return array|Tag[]
     *
     * @throws \Exception
     */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array
    {
        $hasQuery = null !== $query && '' !== $query;
        if ($hasQuery) {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM tag WHERE tenant=:tenant '
                . 'AND (slug ILIKE :q OR label ILIKE :q) '
                . 'ORDER BY created_at DESC LIMIT :l OFFSET :o',
            );
            $stmt->bindValue(':q', '%' . $query . '%');
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM tag WHERE tenant=:tenant ORDER BY created_at DESC LIMIT :l OFFSET :o',
            );
        }
        $stmt->bindValue(':tenant', $tenantId);
        $stmt->bindValue(':l', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map($this->hydrateTag(...), $rows);
    }

    public function deleteTag(string $tenantId, string $id): void
    {
        $this->execute(
            'DELETE FROM tag WHERE tenant=:tenant AND id=:id',
            [':tenant' => $tenantId, ':id' => $id],
        );
    }

    public function saveAssignment(string $tenantId, TagAssignment $a): void
    {
        $sql = 'INSERT INTO tag_assignment(tenant,id,tag_id,assigned_type,assigned_id,created_at) '
            . 'VALUES (:tenant,:id,:tag_id,:type,:aid,:created_at) '
            . 'ON CONFLICT (tenant,id) DO NOTHING';
        $this->execute(
            $sql,
            [
                ':tenant' => $tenantId,
                ':id' => $a->id(),
                ':tag_id' => $a->tagId(),
                ':type' => $a->assignedType(),
                ':aid' => $a->assignedId(),
                ':created_at' => $a->createdAt()->format('c'),
            ],
        );
    }

    public function deleteAssignment(string $tenantId, string $assignmentId): void
    {
        $this->execute(
            'DELETE FROM tag_assignment WHERE tenant=:tenant AND id=:id',
            [':tenant' => $tenantId, ':id' => $assignmentId],
        );
    }

    /**
     * @return array|TagAssignment[]
     *
     * @throws \Exception
     */
    public function listAssignments(
        string $tenantId,
        string $tagId,
        ?string $type = null,
        ?string $assignedId = null,
    ): array {
        $sql = 'SELECT * FROM tag_assignment WHERE tenant=:tenant AND tag_id=:tid';
        $p = [':tenant' => $tenantId, ':tid' => $tagId];
        if (null !== $type) {
            $sql .= ' AND assigned_type=:t';
            $p[':t'] = $type;
        }
        if (null !== $assignedId) {
            $sql .= ' AND assigned_id=:aid';
            $p[':aid'] = $assignedId;
        }
        $rows = $this->fetchAllAssoc($sql, $p);

        return array_map($this->hydrateAssignment(...), $rows);
    }

    public function saveSynonym(string $tenantId, TagSynonym $s): void
    {
        $this->execute(
            'INSERT INTO tag_synonym(tenant,id,tag_id,label) VALUES (:tenant,:id,:tid,:label) '
            . 'ON CONFLICT (tenant,id) DO NOTHING',
            [':tenant' => $tenantId, ':id' => $s->id(), ':tid' => $s->tagId(), ':label' => $s->label()],
        );
    }

    /**
     * @return array|TagSynonym[]
     */
    public function listSynonyms(string $tenantId, string $tagId): array
    {
        $rows = $this->fetchAllAssoc(
            'SELECT * FROM tag_synonym WHERE tenant=:tenant AND tag_id=:tid',
            [':tenant' => $tenantId, ':tid' => $tagId],
        );

        return array_map($this->hydrateSynonym(...), $rows);
    }

    public function saveRelation(string $tenantId, TagRelation $r): void
    {
        $this->execute(
            'INSERT INTO tag_relation(tenant,id,from_tag_id,to_tag_id,type) VALUES (:tenant,:id,:f,:t,:type) '
            . 'ON CONFLICT (tenant,id) DO NOTHING',
            [
                ':tenant' => $tenantId,
                ':id' => $r->id(),
                ':f' => $r->fromTagId(),
                ':t' => $r->toTagId(),
                ':type' => $r->type(),
            ],
        );
    }

    /**
     * @return array|TagRelation[]
     */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array
    {
        $sql = 'SELECT * FROM tag_relation WHERE tenant=:tenant AND from_tag_id=:f';
        $p = [':tenant' => $tenantId, ':f' => $tagId];
        if ($type) {
            $sql .= ' AND type=:type';
            $p[':type'] = $type;
        }
        $rows = $this->fetchAllAssoc($sql, $p);

        return array_map($this->hydrateRelation(...), $rows);
    }

    public function saveScheme(string $tenantId, TagScheme $s): void
    {
        $this->execute(
            'INSERT INTO tag_scheme(tenant,id,name,locale,created_at) VALUES (:tenant,:id,:name,:loc,:created_at)
            ON CONFLICT (tenant,id) DO UPDATE SET name=EXCLUDED.name, locale=EXCLUDED.locale',
            [
                ':tenant' => $tenantId,
                ':id' => $s->id(),
                ':name' => $s->name(),
                ':loc' => $s->locale(),
                ':created_at' => $s->createdAt()->format('c'),
            ],
        );
    }

    /**
     * @throws \Exception
     */
    public function getSchemeByName(string $tenantId, string $name): ?TagScheme
    {
        $r = $this->fetchAssoc(
            'SELECT * FROM tag_scheme WHERE tenant=:tenant AND name=:n',
            [':tenant' => $tenantId, ':n' => $name],
        );

        return is_array($r) ? $this->hydrateScheme($r) : null;
    }

    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void
    {
        $this->execute(
            'UPDATE tag_assignment SET tag_id=:to WHERE tenant=:tenant AND tag_id=:from',
            [':tenant' => $tenantId, ':to' => $toTagId, ':from' => $fromTagId],
        );
    }

    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void
    {
        $this->execute(
            'UPDATE tag SET required_flag=:r, mod_only_flag=:m WHERE tenant=:tenant AND id=:id',
            [
                ':tenant' => $tenantId,
                ':r' => $required ? 1 : 0,
                ':m' => $modOnly ? 1 : 0,
                ':id' => $tagId,
            ],
        );
    }

    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void
    {
        $this->execute(
            'UPDATE tag SET label=:l, slug=:s WHERE tenant=:tenant AND id=:id',
            [':tenant' => $tenantId, ':l' => $newLabel, ':s' => $newSlug, ':id' => $tagId],
        );
    }

    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void
    {
        $this->execute(
            'INSERT INTO tag_proposal(tenant,id,type,payload,status) VALUES (:tenant,:id,:t,:p,\'pending\')',
            [':tenant' => $tenantId, ':id' => $id, ':t' => $type, ':p' => $payloadJson],
        );
    }

    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void
    {
        $this->execute(
            'UPDATE tag_proposal SET status=:s, decided_at=now(), decided_by=:by WHERE tenant=:tenant AND id=:id',
            [':tenant' => $tenantId, ':s' => $status, ':by' => $decidedBy, ':id' => $id],
        );
    }

    public function insertAudit(string $tenantId, TagAuditRecord $record): void
    {
        $this->execute(
            'INSERT INTO tag_audit_log(tenant,id,action,entity_type,entity_id,details) '
            . 'VALUES (:tenant,:id,:a,:et,:eid,:d)',
            [
                ':tenant' => $tenantId,
                ':id' => $record->id,
                ':a' => $record->action,
                ':et' => $record->entityType,
                ':eid' => $record->entityId,
                ':d' => $record->detailsJson,
            ],
        );
    }

    /**
     * @return array|Tag[]
     *
     * @throws \Exception
     */
    public function listAllTags(string $tenantId): array
    {
        $rows = $this->fetchAllAssoc('SELECT * FROM tag WHERE tenant=:tenant', [':tenant' => $tenantId]);

        return array_map($this->hydrateTag(...), $rows);
    }

    /**
     * @throws \JsonException
     */
    public function getPolicy(string $tenantId): array
    {
        $row = $this->fetchAssoc('SELECT policy FROM tag_policy WHERE tenant=:tenant', [':tenant' => $tenantId]);

        return $row ? json_decode($row['policy'], true, 512, JSON_THROW_ON_ERROR) : [];
    }

    /**
     * @throws \JsonException
     */
    public function setPolicy(string $tenantId, array $policy): void
    {
        $this->execute(
            'INSERT INTO tag_policy(tenant,policy) VALUES (:tenant,:p) '
            . 'ON CONFLICT (tenant) DO UPDATE SET policy=EXCLUDED.policy',
            [':tenant' => $tenantId, ':p' => json_encode($policy, JSON_THROW_ON_ERROR)],
        );
    }

    /**
     * @return array|array[]
     */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array
    {
        $sql = 'SELECT t.id as "tagId", t.slug, t.label, v.cnt::int as cnt '
            . 'FROM tag_stats_view v '
            . 'JOIN tag t ON t.tenant=v.tenant AND t.id=v.tag_id '
            . 'WHERE v.tenant=:tenant AND v.assigned_type=:t '
            . 'ORDER BY v.cnt DESC, t.slug ASC LIMIT :l';
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':tenant', $tenantId);
        $st->bindValue(':t', $assignedType);
        $st->bindValue(':l', $limit, \PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array|array[]
     */
    public function tagCloud(string $tenantId, int $limit): array
    {
        $sql = 'SELECT t.id as "tagId", t.slug, t.label, SUM(v.cnt)::int as cnt '
            . 'FROM tag_stats_view v '
            . 'JOIN tag t ON t.tenant=v.tenant AND t.id=v.tag_id '
            . 'WHERE v.tenant=:tenant '
            . 'GROUP BY t.id, t.slug, t.label '
            . 'ORDER BY SUM(v.cnt) DESC LIMIT :l';
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':tenant', $tenantId);
        $st->bindValue(':l', $limit, \PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function putClassification(string $tenantId, TagClassificationRecord $record): void
    {
        $this->execute(
            'INSERT INTO tag_classification(tenant,id,scope,ref_id,key,value) VALUES (:tenant,:id,:scope,:ref,:k,:v) '
            . 'ON CONFLICT (tenant,id) DO UPDATE SET key=EXCLUDED.key, value=EXCLUDED.value',
            [
                ':tenant' => $tenantId,
                ':id' => $record->id,
                ':scope' => $record->scope,
                ':ref' => $record->refId,
                ':k' => $record->key,
                ':v' => $record->value,
            ],
        );
    }

    /**
     * @return array|array[]
     */
    public function listClassifications(string $tenantId, string $scope, string $refId): array
    {
        return $this->fetchAllAssoc(
            'SELECT key,value FROM tag_classification WHERE tenant=:tenant AND scope=:s AND ref_id=:r',
            [':tenant' => $tenantId, ':s' => $scope, ':r' => $refId],
        );
    }

    public function putEffect(string $tenantId, TagEffectRecord $record): void
    {
        $this->execute(
            'INSERT INTO tag_assignment_effect(tenant,id,assigned_type,assigned_id,key,value,source_scope,source_id) '
            . 'VALUES (:tenant,:id,:t,:a,:k,:v,:ss,:sid) ON CONFLICT (tenant,id) DO NOTHING',
            [
                ':tenant' => $tenantId,
                ':id' => $record->id,
                ':t' => $record->assignedType,
                ':a' => $record->assignedId,
                ':k' => $record->key,
                ':v' => $record->value,
                ':ss' => $record->sourceScope,
                ':sid' => $record->sourceId,
            ],
        );
    }

    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void
    {
        $this->execute(
            'DELETE FROM tag_assignment_effect WHERE tenant=:tenant AND source_scope=:s AND source_id=:id',
            [':tenant' => $tenantId, ':s' => $sourceScope, ':id' => $sourceId],
        );
    }

    /**
     * @return array|array[]
     */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array
    {
        return $this->fetchAllAssoc(
            'SELECT assigned_type, assigned_id FROM tag_assignment WHERE tenant=:tenant AND tag_id=:t',
            [':tenant' => $tenantId, ':t' => $tagId],
        );
    }

    /**
     * @return array|array[]
     */
    public function listTagsByScheme(string $tenantId, string $schemeName): array
    {
        return $this->fetchAllAssoc(
            'SELECT t.id as tag_id FROM tag t '
            . 'JOIN tag_scheme s ON s.tenant=t.tenant '
            . 'WHERE t.tenant=:tenant AND s.name=:n',
            [':tenant' => $tenantId, ':n' => $schemeName],
        );
    }
}
