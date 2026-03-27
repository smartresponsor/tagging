<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

require_once __DIR__ . '/IntegrationDbTestCase.php';

use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\AssignService;
use App\Service\Core\Tag\IdempotencyStore;

final class TagIdempotencyConflictTest extends IntegrationDbTestCase
{
    public function testAssignRejectsSameIdempotencyKeyWithDifferentPayload(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-idem-conflict', 'tenant-idem-conflict', 'idem-conflict', 'Idem Conflict')");

        $service = new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));

        $first = $service->assign('tenant-idem-conflict', 'tag-idem-conflict', 'product', 'p-2001', 'idem-conflict-key-1');
        $conflict = $service->assign('tenant-idem-conflict', 'tag-idem-conflict', 'product', 'p-2002', 'idem-conflict-key-1');

        $linkCount = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-idem-conflict'")->fetchColumn();
        $outboxCount = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-idem-conflict' AND topic='tag.assigned'")->fetchColumn();

        self::assertSame(['ok' => true], $first);
        self::assertSame(['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'], $conflict);
        self::assertSame(1, $linkCount);
        self::assertSame(1, $outboxCount);
    }
}
