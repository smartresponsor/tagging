<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Infrastructure\Persistence\Tag;

use App\Tagging\Entity\Core\Tag\Tag;
use App\Tagging\Entity\Core\Tag\TagAssignment;
use App\Tagging\Entity\Core\Tag\TagAssignmentEffect;
use App\Tagging\Entity\Core\Tag\TagAuditLog;
use App\Tagging\Entity\Core\Tag\TagClassification;
use App\Tagging\Entity\Core\Tag\TagPolicy;
use App\Tagging\Entity\Core\Tag\TagProposal;
use App\Tagging\Entity\Core\Tag\TagRelation;
use App\Tagging\Entity\Core\Tag\TagScheme;
use App\Tagging\Entity\Core\Tag\TagSynonym;
use App\Tagging\Entity\Core\Tag\TagLink;
use App\Tagging\Service\Core\Record\TagAuditRecord;
use App\Tagging\Service\Core\Record\TagClassificationRecord;
use App\Tagging\Service\Core\Record\TagEffectRecord;
use App\Tagging\Service\Core\TagRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTagRepository implements TagRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function saveTag(string $tenantId, Tag $tag): void
    {
        $existing = $this->getManagedTag($tenantId, $tag->id());
        if ($existing instanceof Tag) {
            $existing->rename($tag->label());
            $existing->changeSlug($tag->slug());
            $existing->setFlags($tag->requiredFlag(), $tag->modOnlyFlag());
            $this->flushSafely();

            return;
        }

        $this->entityManager->persist($tag);
        $this->flushSafely();
    }

    public function getById(string $tenantId, string $id): ?Tag
    {
        return $this->entityManager->getRepository(Tag::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $id,
        ]);
    }

    public function getBySlug(string $tenantId, string $slug): ?Tag
    {
        return $this->entityManager->getRepository(Tag::class)->findOneBy([
            'tenant' => $tenantId,
            'slug' => $slug,
        ]);
    }

    public function existsSlug(string $tenantId, string $slug, ?string $excludeTagId = null): bool
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(Tag::class, 't')
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
            ->from(Tag::class, 't')
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
        if ($tag instanceof Tag) {
            $this->entityManager->remove($tag);
            $this->flushSafely();
        }
    }

    public function saveAssignment(string $tenantId, TagAssignment $a): void
    {
        if (null !== $this->entityManager->getRepository(TagAssignment::class)->findOneBy([
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
        $entity = $this->entityManager->getRepository(TagAssignment::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $assignmentId,
        ]);
        if ($entity instanceof TagAssignment) {
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
            ->from(TagAssignment::class, 'a')
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

    public function saveSynonym(string $tenantId, TagSynonym $s): void
    {
        if (null !== $this->entityManager->getRepository(TagSynonym::class)->findOneBy([
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
            ->from(TagSynonym::class, 's')
            ->where('s.tenant = :tenant')
            ->andWhere('s.tagId = :tagId')
            ->orderBy('s.label', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getResult();
    }

    public function saveRelation(string $tenantId, TagRelation $r): void
    {
        if (null !== $this->entityManager->getRepository(TagRelation::class)->findOneBy([
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
            ->from(TagRelation::class, 'r')
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

    public function saveScheme(string $tenantId, TagScheme $s): void
    {
        if (null !== $this->entityManager->getRepository(TagScheme::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $s->id(),
        ])) {
            return;
        }

        $this->entityManager->persist($s);
        $this->flushSafely();
    }

    public function getSchemeByName(string $tenantId, string $name): ?TagScheme
    {
        return $this->entityManager->getRepository(TagScheme::class)->findOneBy([
            'tenant' => $tenantId,
            'name' => $name,
        ]);
    }

    public function reassignAssignments(string $tenantId, string $fromTagId, string $toTagId): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(TagAssignment::class, 'a')
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
        if (!$tag instanceof Tag) {
            return;
        }

        $tag->setFlags($required, $modOnly);
        $this->flushSafely();
    }

    public function renameTag(string $tenantId, string $tagId, string $newLabel, string $newSlug): void
    {
        $tag = $this->getById($tenantId, $tagId);
        if (!$tag instanceof Tag) {
            return;
        }

        $tag->rename($newLabel);
        $tag->changeSlug($newSlug);
        $this->flushSafely();
    }

    public function insertProposal(string $tenantId, string $id, string $type, string $payloadJson): void
    {
        if (null !== $this->entityManager->getRepository(TagProposal::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $id,
        ])) {
            return;
        }

        $this->entityManager->persist(new TagProposal(
            $tenantId,
            $id,
            $type,
            json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR) ?: [],
        ));
        $this->flushSafely();
    }

    public function updateProposalStatus(string $tenantId, string $id, string $status, ?string $decidedBy): void
    {
        $proposal = $this->entityManager->getRepository(TagProposal::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $id,
        ]);
        if (!$proposal instanceof TagProposal) {
            return;
        }

        $proposal->setStatus($status, $decidedBy);
        $this->flushSafely();
    }

    public function insertAudit(string $tenantId, TagAuditRecord $record): void
    {
        if (null !== $this->entityManager->getRepository(TagAuditLog::class)->findOneBy([
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

        $this->entityManager->persist(new TagAuditLog(
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
            ->from(Tag::class, 't')
            ->where('t.tenant = :tenant')
            ->orderBy('t.slug', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->getQuery()
            ->getResult();
    }

    public function getPolicy(string $tenantId): array
    {
        $policy = $this->entityManager->getRepository(TagPolicy::class)->find($tenantId);
        if (!$policy instanceof TagPolicy) {
            return [];
        }

        return $policy->policy();
    }

    public function setPolicy(string $tenantId, array $policy): void
    {
        $existing = $this->entityManager->getRepository(TagPolicy::class)->find($tenantId);
        if ($existing instanceof TagPolicy) {
            $existing->setPolicy($policy);
        } else {
            $this->entityManager->persist(new TagPolicy($tenantId, $policy));
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
            ->from(TagLink::class, 'l')
            ->join(Tag::class, 't', 'WITH', 't.tenant = l.tenant AND t.id = l.tagId')
            ->where('l.tenant = :tenant')
            ->andWhere('l.entityType = :assignedType')
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
            ->from(TagLink::class, 'l')
            ->join(Tag::class, 't', 'WITH', 't.tenant = l.tenant AND t.id = l.tagId')
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
        if ($existing instanceof TagClassification) {
            $existing->setValue($record->value);
        } else {
            $this->entityManager->persist(new TagClassification(
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
            ->from(TagClassification::class, 'c')
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
        if (null !== $this->entityManager->getRepository(TagAssignmentEffect::class)->findOneBy([
            'tenant' => $tenantId,
            'id' => $record->id,
        ])) {
            return;
        }

        $this->entityManager->persist(new TagAssignmentEffect(
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
            ->delete(TagAssignmentEffect::class, 'e')
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
            ->from(TagAssignment::class, 'a')
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
        if (!$scheme instanceof TagScheme) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('t.id AS tag_id')
            ->from(Tag::class, 't')
            ->where('t.tenant = :tenant')
            ->orderBy('t.slug', 'ASC')
            ->setParameter('tenant', $tenantId)
            ->getQuery()
            ->getArrayResult();
    }

    private function getManagedTag(string $tenantId, string $tagId): ?Tag
    {
        $tag = $this->getById($tenantId, $tagId);

        return $tag instanceof Tag ? $tag : null;
    }

    private function findClassification(string $tenantId, string $scope, string $refId, string $key): ?TagClassification
    {
        return $this->entityManager->getRepository(TagClassification::class)->findOneBy([
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
