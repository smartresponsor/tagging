<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Infrastructure\Persistence\Tag;

use App\Tagging\Entity\Projection\Tag\TagAssignmentEffectProjection;
use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagAuditLogEntity;
use App\Tagging\Entity\Tag\TagClassificationEntity;
use App\Tagging\Entity\Tag\TagPolicyEntity;
use App\Tagging\Entity\Tag\TagProposalEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Entity\Tag\TagSchemeEntity;
use App\Tagging\Entity\Tag\TagSynonymEntity;
use App\Tagging\Service\Core\Record\TagAuditRecord;
use App\Tagging\Service\Core\Record\TagClassificationRecord;
use App\Tagging\Service\Core\Record\TagEffectRecord;
use App\Tagging\Service\Core\Record\TagEntityCreateRecord;
use App\Tagging\Service\Core\TagRepositoryInterface;
use App\Tagging\Service\Core\TagCrudRepositoryInterface;
use App\Tagging\Service\Core\TagPolicyRepositoryInterface;
use App\Tagging\Service\Core\TagReadRepositoryInterface;
use App\Tagging\Service\Core\TagWriteRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class TagDoctrineRepository implements TagRepositoryInterface, TagCrudRepositoryInterface, TagReadRepositoryInterface, TagWriteRepositoryInterface, TagPolicyRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function saveTag(string $tenantId, TagEntity $tag): void
    {
        $existing = $this->getManagedTag($tenantId, $tag->id());
        if ($existing instanceof TagEntity) {
            $existing->rename($tag->label());
            $existing->changeSlug($tag->slug());
            $existing->setFlags($tag->requiredFlag(), $tag->modOnlyFlag());
            $this->flushSafely();

            return;
        }

        $this->entityManager->persist($tag);
        $this->flushSafely();
    }

    public function getById(string $tenantId, string $id): ?TagEntity
    {
        return $this->entityManager->getRepository(TagEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $id,
        ]);
    }

    public function getBySlug(string $tenantId, string $slug): ?TagEntity
    {
        return $this->entityManager->getRepository(TagEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'slug' => $slug,
        ]);
    }

    public function existsSlug(string $tenantId, string $slug, ?string $excludeTagId = null): bool
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(TagEntity::class, 't')
            ->where('t.tenant = :tenant')
            ->andWhere('t.slug = :slug')
            ->setParameter('tenant', $tenantId)
            ->setParameter('slug', $slug);

        if (null !== $excludeTagId && '' !== $excludeTagId) {
            $qb->andWhere('t.id <> :exclude')->setParameter('exclude', $excludeTagId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function i18nSlugExists(string $tenantId, string $locale, string $slug, ?string $excludeTagId = null): bool
    {
        return $this->existsSlug($tenantId, $slug, $excludeTagId);
    }

    /**
     * @return Tag[]
     */
    public function search(string $tenantId, ?string $query, int $limit, int $offset): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(TagEntity::class, 't')
            ->where('t.tenant = :tenant')
            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult(max(0, $offset))
            ->setMaxResults(max(1, $limit))
            ->setParameter('tenant', $tenantId);

        if (null !== $query && '' !== $query) {
            $qb->andWhere('LOWER(t.slug) LIKE :query OR LOWER(t.label) LIKE :query')
                ->setParameter('query', '%' . mb_strtolower($query) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function deleteTag(string $tenantId, string $id): void
    {
        $tag = $this->getById($tenantId, $id);
        if ($tag instanceof TagEntity) {
            $this->entityManager->remove($tag);
            $this->flushSafely();
        }
    }

    public function saveAssignment(string $tenantId, TagAssignmentEntity $a): void
    {
        if (null !== $this->entityManager->getRepository(TagAssignmentEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $a->id(),
        ])) {
            return;
        }

        $this->entityManager->persist($a);
        $this->flushSafely();
    }

    public function deleteAssignment(string $tenantId, string $assignmentId): void
    {
        $entity = $this->entityManager->getRepository(TagAssignmentEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $assignmentId,
        ]);
        if ($entity instanceof TagAssignmentEntity) {
            $this->entityManager->remove($entity);
            $this->flushSafely();
        }
    }

    /**
     * @return TagAssignment[]
     */
    public function listAssignments(
        string $tenantId,
        string $tagId,
        ?string $type = null,
        ?string $assignedId = null,
    ): array {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(TagAssignmentEntity::class, 'a')
            ->where('a.tenant = :tenant')
            ->andWhere('a.tagId = :tagId')
            ->setParameter('tenant', $tenantId)
            ->setParameter('tagId', $tagId)
            ->orderBy('a.createdAt', 'ASC');

        if (null !== $type) {
            $qb->andWhere('a.assignedType = :type')->setParameter('type', $type);
        }
        if (null !== $assignedId) {
            $qb->andWhere('a.assignedId = :assignedId')->setParameter('assignedId', $assignedId);
        }

        return $qb->getQuery()->getResult();
    }

    public function saveSynonym(string $tenantId, TagSynonymEntity $s): void
    {
        if (null !== $this->entityManager->getRepository(TagSynonymEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $s->id(),
        ])) {
            return;
        }

        $this->entityManager->persist($s);
        $this->flushSafely();
    }

    /**
     * @return TagSynonym[]
     */
    public function listSynonyms(string $tenantId, string $tagId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(TagSynonymEntity::class, 's')
            ->where('s.tenant = :tenant')
            ->andWhere('s.tagId = :tagId')
            ->orderBy('s.label', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getResult();
    }

    public function saveRelation(string $tenantId, TagRelationEntity $r): void
    {
        if (null !== $this->entityManager->getRepository(TagRelationEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $r->id(),
        ])) {
            return;
        }

        $this->entityManager->persist($r);
        $this->flushSafely();
    }

    /**
     * @return TagRelation[]
     */
    public function listRelations(string $tenantId, string $tagId, ?string $type = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(TagRelationEntity::class, 'r')
            ->where('r.tenant = :tenant')
            ->andWhere('r.fromTagId = :tagId')
            ->setParameter('tenant', $tenantId)
            ->setParameter('tagId', $tagId)
            ->orderBy('r.type', 'ASC');

        if (null !== $type) {
            $qb->andWhere('r.type = :type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }

    public function saveScheme(string $tenantId, TagSchemeEntity $s): void
    {
        if (null !== $this->entityManager->getRepository(TagSchemeEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $s->id(),
        ])) {
            return;
        }

        $this->entityManager->persist($s);
        $this->flushSafely();
    }

    public function getSchemeByName(string $tenantId, string $nameEntity): ?TagSchemeEntity
    {
        return $this->entityManager->getRepository(TagSchemeEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'nameEntity' => $nameEntity,
        ]);
    }

    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(TagAssignmentEntity::class, 'a')
            ->set('a.tagId', ':to')
            ->where('a.tenant = :tenant')
            ->andWhere('a.tagId = :from')
            ->setParameter('tenant', $tenantId)
            ->setParameter('to', $toTagId)
            ->setParameter('from', $fromTagId)
            ->getQuery()
            ->execute();
    }

    public function setTagFlags(string $tenantId, string $tagId, bool $required, bool $modOnly): void
    {
        $tag = $this->getById($tenantId, $tagId);
        if (!$tag instanceof TagEntity) {
            return;
        }

        $tag->setFlags($required, $modOnly);
        $this->flushSafely();
    }

    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void
    {
        $tag = $this->getById($tenantId, $tagId);
        if (!$tag instanceof TagEntity) {
            return;
        }

        $tag->rename($newLabel);
        $tag->changeSlug($newSlug);
        $this->flushSafely();
    }

    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void
    {
        if (null !== $this->entityManager->getRepository(TagProposalEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $id,
        ])) {
            return;
        }

        $this->entityManager->persist(new TagProposalEntity(
            $tenantId,
            $id,
            $type,
            json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR) ?: [],
        ));
        $this->flushSafely();
    }

    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void
    {
        $proposal = $this->entityManager->getRepository(TagProposalEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $id,
        ]);
        if (!$proposal instanceof TagProposalEntity) {
            return;
        }

        $proposal->setStatus($status, $decidedBy);
        $this->flushSafely();
    }

    public function insertAudit(string $tenantId, TagAuditRecord $record): void
    {
        if (null !== $this->entityManager->getRepository(TagAuditLogEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $record->id,
        ])) {
            return;
        }

        $details = [];
        try {
            $details = json_decode($record->detailsJson, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($details)) {
                $details = [];
            }
        } catch (\Throwable) {
            $details = [];
        }

        $this->entityManager->persist(new TagAuditLogEntity(
            $tenantId,
            $record->id,
            $record->action,
            $record->entityType,
            $record->entityId,
            $details,
        ));
        $this->flushSafely();
    }

    /**
     * @return Tag[]
     */
    public function listAllTags(string $tenantId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(TagEntity::class, 't')
            ->where('t.tenant = :tenant')
            ->orderBy('t.slug', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->getQuery()
            ->getResult();
    }

    public function getPolicy(string $tenantId): array
    {
        $policy = $this->entityManager->getRepository(TagPolicyEntity::class)->find($tenantId);
        if (!$policy instanceof TagPolicyEntity) {
            return [];
        }

        return $policy->policy();
    }

    public function setPolicy(string $tenantId, array $policy): void
    {
        $existing = $this->entityManager->getRepository(TagPolicyEntity::class)->find($tenantId);
        if ($existing instanceof TagPolicyEntity) {
            $existing->setPolicy($policy);
        } else {
            $this->entityManager->persist(new TagPolicyEntity($tenantId, $policy));
        }

        $this->flushSafely();
    }

    /**
     * @return array<int, array{tagId:string, slug:string, label:string, cnt:int}>
     */
    public function facetTop(string $tenantId, string $assignedType, int $limit): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t.id AS tagId, t.slug AS slug, t.label AS label, COUNT(l.tagId) AS cnt')
            ->from(TagAssignmentEntity::class, 'l')
            ->join(TagEntity::class, 't', 'WITH', 't.tenant = l.tenant AND t.id = l.tagId')
            ->where('l.tenant = :tenant')
            ->andWhere('l.assignedType = :assignedType')
            ->groupBy('t.id, t.slug, t.label')
            ->orderBy('cnt', 'DESC')
            ->addOrderBy('t.slug', 'ASC')
            ->setMaxResults(max(1, $limit))
            ->setParameter('tenant', $tenantId)
            ->setParameter('assignedType', $assignedType)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array{tagId:string, slug:string, label:string, cnt:int}>
     */
    public function tagCloud(string $tenantId, int $limit): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t.id AS tagId, t.slug AS slug, t.label AS label, COUNT(l.tagId) AS cnt')
            ->from(TagAssignmentEntity::class, 'l')
            ->join(TagEntity::class, 't', 'WITH', 't.tenant = l.tenant AND t.id = l.tagId')
            ->where('l.tenant = :tenant')
            ->groupBy('t.id, t.slug, t.label')
            ->orderBy('cnt', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->setParameter('tenant', $tenantId)
            ->getQuery()
            ->getArrayResult();
    }

    public function putClassification(string $tenantId, TagClassificationRecord $record): void
    {
        $existing = $this->findClassification($tenantId, $record->scope, $record->refId, $record->key);
        if ($existing instanceof TagClassificationEntity) {
            $existing->setValue($record->value);
        } else {
            $this->entityManager->persist(new TagClassificationEntity(
                $tenantId,
                $record->id,
                $record->scope,
                $record->refId,
                $record->key,
                $record->value,
            ));
        }

        $this->flushSafely();
    }

    /**
     * @return array<int, array{key:string,value:string}>
     */
    public function listClassifications(string $tenantId, string $scope, string $refId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('c.key AS key, c.value AS value')
            ->from(TagClassificationEntity::class, 'c')
            ->where('c.tenant = :tenant')
            ->andWhere('c.scope = :scope')
            ->andWhere('c.refId = :refId')
            ->orderBy('c.key', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->setParameter('scope', $scope)
            ->setParameter('refId', $refId)
            ->getQuery()
            ->getArrayResult();
    }

    public function putEffect(string $tenantId, TagEffectRecord $record): void
    {
        if (null !== $this->entityManager->getRepository(TagAssignmentEffectProjection::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $record->id,
        ])) {
            return;
        }

        $this->entityManager->persist(new TagAssignmentEffectProjection(
            $tenantId,
            $record->id,
            $record->assignedType,
            $record->assignedId,
            $record->key,
            $record->value,
            $record->sourceScope,
            $record->sourceId,
        ));
        $this->flushSafely();
    }

    public function clearEffectsForSource(string $tenantId, string $sourceScope, string $sourceId): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(TagAssignmentEffectProjection::class, 'e')
            ->where('e.tenant = :tenant')
            ->andWhere('e.sourceScope = :scope')
            ->andWhere('e.sourceId = :sourceId')
            ->setParameter('tenant', $tenantId)
            ->setParameter('scope', $sourceScope)
            ->setParameter('sourceId', $sourceId)
            ->getQuery()
            ->execute();
    }

    /**
     * @return array<int, array{assigned_type:string,assigned_id:string}>
     */
    public function listAssignmentsByTag(string $tenantId, string $tagId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('a.assignedType AS assigned_type, a.assignedId AS assigned_id')
            ->from(TagAssignmentEntity::class, 'a')
            ->where('a.tenant = :tenant')
            ->andWhere('a.tagId = :tagId')
            ->orderBy('a.assignedType', 'ASC')
            ->addOrderBy('a.assignedId', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array{tag_id:string}>
     */
    public function listTagsByScheme(string $tenantId, string $schemeName): array
    {
        $scheme = $this->getSchemeByName($tenantId, $schemeName);
        if (!$scheme instanceof TagSchemeEntity) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('t.id AS tag_id')
            ->from(TagEntity::class, 't')
            ->where('t.tenant = :tenant')
            ->orderBy('t.slug', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->getQuery()
            ->getArrayResult();
    }


    public function findById(string $tenant, string $id): ?array
    {
        $entity = $this->getById($tenant, $id);

        return $entity instanceof TagEntity ? $this->toCrudArray($entity) : null;
    }

    public function create(string $tenant, TagEntityCreateRecord $record): array
    {
        $entity = TagEntity::create(
            tenant: $tenant,
            id: $record->id,
            slug: $record->slug,
            label: $record->nameEntity,
            locale: $record->locale,
            weight: $record->weight,
        );

        $this->saveTag($tenant, $entity);

        return $this->toCrudArray($entity);
    }

    public function patch(string $tenant, string $id, array $patch): void
    {
        $entity = $this->getById($tenant, $id);
        if (!$entity instanceof TagEntity) {
            return;
        }

        $entity->patch($patch);
        $this->flushSafely();
    }

    public function delete(string $tenant, string $id): void
    {
        $this->deleteTag($tenant, $id);
    }

    /**
     * @return array{
     *     id:string,
     *     slug:string,
     *     name:string,
     *     locale:string,
     *     weight:int,
     *     required_flag:bool,
     *     mod_only_flag:bool,
     *     created_at:string,
     *     updated_at:string
     * }
     */
    private function toCrudArray(TagEntity $entity): array
    {
        return [
            'id' => $entity->id(),
            'slug' => $entity->slug(),
            'nameEntity' => $entity->label(),
            'locale' => (string) ($entity->locale() ?? ''),
            'weight' => $entity->weight(),
            'required_flag' => $entity->requiredFlag(),
            'mod_only_flag' => $entity->modOnlyFlag(),
            'created_at' => $entity->createdAt()->format(DATE_ATOM),
            'updated_at' => $entity->updatedAt()?->format(DATE_ATOM) ?? '',
        ];
    }
    private function getManagedTag(string $tenantId, string $tagId): ?TagEntity
    {
        $tag = $this->getById($tenantId, $tagId);

        return $tag instanceof TagEntity ? $tag : null;
    }

    private function findClassification(string $tenantId, string $scope, string $refId, string $key): ?TagClassificationEntity
    {
        return $this->entityManager->getRepository(TagClassificationEntity::class)->findOneBy([
            'tenant' => $tenantId,
            'scope' => $scope,
            'refId' => $refId,
            'key' => $key,
        ]);
    }

    private function flushSafely(): void
    {
        try {
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            if ($this->entityManager->isOpen()) {
                $this->entityManager->clear();
            }
            if (str_contains($e::class, 'UniqueConstraintViolation')) {
                throw new \RuntimeException('slug_conflict', 0, $e);
            }

            throw $e;
        }
    }
}
