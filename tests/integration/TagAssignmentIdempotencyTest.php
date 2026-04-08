<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

require_once __DIR__ . '/IntegrationDbTestCase.php';

use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\AssignService;
use App\Service\Core\Tag\IdempotencyStore;

final class TagAssignmentIdempotencyTest extends IntegrationDbTestCase
{
    public function testAssignWithSameIdempotencyKeyIsStableOnRepeat(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            "INSERT INTO tag_entity (id, tenant, slug, name) "
            . "VALUES ('tag-idem', 'tenant-idem', 'idem', 'Idem')",
        );
        $service = new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));
        $first = $service->assign('tenant-idem', 'tag-idem', 'product', 'p-1001', 'idem-key-1');
        $second = $service->assign('tenant-idem', 'tag-idem', 'product', 'p-1001', 'idem-key-1');
        $linkCount = (int) $pdo
            ->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-idem'")
            ->fetchColumn();
        $outboxCount = (int) $pdo
            ->query(
                "SELECT COUNT(*) FROM outbox_event "
                . "WHERE tenant='tenant-idem' AND topic='tag.assigned'",
            )
            ->fetchColumn();
        $status = $pdo
            ->query(
                "SELECT status FROM idempotency_store "
                . "WHERE tenant='tenant-idem' AND key='idem-key-1'",
            )
            ->fetchColumn();

        self::assertSame(['ok' => true], $first);
        self::assertSame(['ok' => true, 'duplicated' => true], $second);
        self::assertSame(1, $linkCount);
        self::assertSame(1, $outboxCount);
        self::assertSame('done', $status);
    }

    public function testAssignWithSameIdempotencyKeyAndDifferentPayloadReturnsConflict(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            "INSERT INTO tag_entity (id, tenant, slug, name) VALUES ("
            . "'tag-idem-conflict', 'tenant-idem-conflict', 'idem-conflict', 'Idem Conflict')",
        );

        $service = new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));

        $first = $service->assign(
            'tenant-idem-conflict',
            'tag-idem-conflict',
            'product',
            'p-1001',
            'idem-key-conflict',
        );
        $second = $service->assign(
            'tenant-idem-conflict',
            'tag-idem-conflict',
            'product',
            'p-1002',
            'idem-key-conflict',
        );

        self::assertSame(['ok' => true], $first);
        self::assertSame(['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'], $second);
    }
}
