<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Data\Model\Tag\TagEntity;
use App\Tagging\Entity\Core\Tag\TagLink;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagQuotaService
{
    private TagErrorSink $errorSink;

    public function __construct(
        private ?EntityManagerInterface $entityManager = null,
        private array $cfg = [],
        TagErrorSink|callable|null $errorSink = null,
    ) {
        $this->errorSink = TagErrorSinkFactory::from($errorSink);
    }

    public function canCreateTag(string $tenantId): array
    {
        return $this->quotaResult(
            $tenantId,
            (int) ($this->cfg['quotas']['max_tags'] ?? 0),
            'quota_tags_exceeded',
            static fn(EntityManagerInterface $entityManager, string $tenantId): int => (int) $entityManager
                ->createQueryBuilder()
                ->select('COUNT(e.id)')
                ->from(TagEntity::class, 'e')
                ->where('e.tenant = :tenant')
                ->setParameter('tenant', $tenantId)
                ->getQuery()
                ->getSingleScalarResult(),
        );
    }

    public function canAssign(string $tenantId): array
    {
        return $this->quotaResult(
            $tenantId,
            (int) ($this->cfg['quotas']['max_assignments'] ?? 0),
            'quota_assignments_exceeded',
            static fn(EntityManagerInterface $entityManager, string $tenantId): int => (int) $entityManager
                ->createQueryBuilder()
                ->select('COUNT(l.tenant)')
                ->from(TagLink::class, 'l')
                ->where('l.tenant = :tenant')
                ->setParameter('tenant', $tenantId)
                ->getQuery()
                ->getSingleScalarResult(),
        );
    }

    public function assertCanCreateTag(string $tenantId): void
    {
        $this->assertAllowed($this->canCreateTag($tenantId));
    }

    public function assertCanAssign(string $tenantId): void
    {
        $this->assertAllowed($this->canAssign($tenantId));
    }

    /**
     * @param callable(EntityManagerInterface,string):int $counter
     */
    private function quotaResult(string $tenantId, int $max, string $code, callable $counter): array
    {
        if ($max <= 0) {
            return ['ok' => true, 'used' => 0, 'max' => 0, 'remaining' => null, 'code' => null];
        }

        $count = $this->countByCounter($counter, $tenantId);
        $ok = $count < $max;

        return [
            'ok' => $ok,
            'used' => $count,
            'max' => $max,
            'remaining' => max(0, $max - $count),
            'code' => $ok ? null : $code,
        ];
    }

    private function assertAllowed(array $result): void
    {
        if (($result['ok'] ?? false) === true) {
            return;
        }

        throw new \RuntimeException((string) ($result['code'] ?? 'quota_exceeded'));
    }

    /**
     * @param callable(EntityManagerInterface,string):int $counter
     */
    private function countByCounter(callable $counter, string $tenantId): int
    {
        if (null === $this->entityManager) {
            return 0;
        }

        try {
            return $counter($this->entityManager, $tenantId);
        } catch (\Throwable $e) {
            $this->report($e, ['tenant' => $tenantId]);

            return 0;
        }
    }

    private function report(\Throwable $e, array $context = []): void
    {
        $this->errorSink->report([
            'code' => 'quota.count_failed',
            'message' => $e->getMessage(),
            'exception' => $e::class,
            'context' => $context,
        ]);
    }
}
