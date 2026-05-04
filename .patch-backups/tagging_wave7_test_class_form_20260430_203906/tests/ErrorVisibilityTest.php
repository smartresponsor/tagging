<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Http\Api\Tag\TagStatusController;
use App\Tagging\Infrastructure\Outbox\Tag\TagOutboxPublisher;
use App\Tagging\Service\Core\TagAssignService;
use App\Tagging\Service\Core\TagQuotaService;
use App\Tagging\Service\Core\TagEntityRepositoryInterface;
use App\Tagging\Service\Core\TagUnassignService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use PHPUnit\Framework\TestCase;

final class ErrorVisibilityTest extends TestCase
{
    public function testStatusControllerReportsProbeFailuresToErrorSink(): void
    {
        $errors = [];
        $controller = new TagStatusController(
            static function (): bool {
                throw new \RuntimeException('db down');
            },
            'test-version',
            static function (array $error) use (&$errors): void {
                $errors[] = $error;
            },
        );

        $payload = $controller->status();

        self::assertTrue($payload['ok']);
        self::assertFalse($payload['db']['ok']);
        self::assertSame('db_unavailable', $payload['db']['error']);
        self::assertCount(1, $errors);
        self::assertSame('status.db_probe_failed', $errors[0]['code']);
    }

    public function testQuotaServiceReportsQueryFailuresToErrorSink(): void
    {
        $errors = [];
        $entityManager = $this->quotaEntityManagerMock();
        $service = new TagQuotaService(
            $entityManager,
            ['quotas' => ['max_tags' => 5]],
            static function (array $error) use (&$errors): void {
                $errors[] = $error;
            },
        );

        $result = $service->canCreateTag('tenant-a');

        self::assertTrue($result['ok']);
        self::assertSame(0, $result['used']);
        self::assertSame(5, $result['max']);
        self::assertCount(1, $errors);
        self::assertSame('quota.count_failed', $errors[0]['code']);
    }

    public function testAssignServiceReportsFailuresToErrorSinkAndReturnsCode(): void
    {
        $errors = [];
        $entityManager = $this->failingEntityManager();
        $tagRepo = $this->createMock(TagEntityRepositoryInterface::class);
        $tagRepo->method('findById')->willThrowException(new \RuntimeException('boom'));
        $service = new TagAssignService(
            $entityManager,
            $tagRepo,
            new TagOutboxPublisher($entityManager),
            null,
            static function (array $error) use (&$errors): void {
                $errors[] = $error;
            },
        );

        $result = $service->assign('tenant-a', 'tag-1', 'file', 'file-1');

        self::assertFalse($result['ok']);
        self::assertSame('assign_failed', $result['code']);
        self::assertCount(1, $errors);
        self::assertSame('tag.assign_failed', $errors[0]['code']);
    }

    public function testUnassignServiceReportsFailuresToErrorSinkAndReturnsCode(): void
    {
        $errors = [];
        $entityManager = $this->failingEntityManager();
        $tagRepo = $this->createMock(TagEntityRepositoryInterface::class);
        $tagRepo->method('findById')->willThrowException(new \RuntimeException('boom'));
        $service = new TagUnassignService(
            $entityManager,
            $tagRepo,
            new TagOutboxPublisher($entityManager),
            null,
            static function (array $error) use (&$errors): void {
                $errors[] = $error;
            },
        );

        $result = $service->unassign('tenant-a', 'tag-1', 'file', 'file-1');

        self::assertFalse($result['ok']);
        self::assertSame('unassign_failed', $result['code']);
        self::assertCount(1, $errors);
        self::assertSame('tag.unassign_failed', $errors[0]['code']);
    }

    private function failingEntityManager(): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $connection->method('isTransactionActive')->willReturn(true);
        $entityManager->method('getConnection')->willReturn($connection);

        return $entityManager;
    }

    private function quotaEntityManagerMock(): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $qb = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $entityManager->method('createQueryBuilder')->willReturn($qb);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willThrowException(new \RuntimeException('db error'));

        return $entityManager;
    }
}
