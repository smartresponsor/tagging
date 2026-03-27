<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\StatusController;
use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\AssignService;
use App\Service\Core\Tag\QuotaService;
use App\Service\Core\Tag\UnassignService;
use PHPUnit\Framework\TestCase;

final class ErrorVisibilityTest extends TestCase
{
    use RequiresSqlite;

    public function testStatusControllerReportsProbeFailuresToErrorSink(): void
    {
        $errors = [];
        $controller = new StatusController(
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
        $this->requireSqlite();

        $errors = [];
        $pdo = new \PDO('sqlite::memory:');
        $service = new QuotaService(
            $pdo,
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
        $this->requireSqlite();

        $errors = [];
        $pdo = new \PDO('sqlite::memory:');
        $service = new AssignService(
            $pdo,
            new OutboxPublisher($pdo),
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
        $this->requireSqlite();

        $errors = [];
        $pdo = new \PDO('sqlite::memory:');
        $service = new UnassignService(
            $pdo,
            new OutboxPublisher($pdo),
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
}
