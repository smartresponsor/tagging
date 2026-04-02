<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

require_once __DIR__ . '/IntegrationDbTestCase.php';

use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\IdempotencyStore;
use App\Service\Core\Tag\UnassignService;

final class TagUnassignIdempotencyTest extends IntegrationDbTestCase
{
    public function testUnassignWithSameIdempotencyKeyIsStableOnRepeat(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-unassign-idem', 'tenant-unassign-idem', 'idem', 'Idem')");
        $pdo->exec("INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES ('tenant-unassign-idem', 'product', 'p-1001', 'tag-unassign-idem')");

        $service = new UnassignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));

        $first = $service->unassign('tenant-unassign-idem', 'tag-unassign-idem', 'product', 'p-1001', 'idem-unassign-1');
        $second = $service->unassign('tenant-unassign-idem', 'tag-unassign-idem', 'product', 'p-1001', 'idem-unassign-1');

        $linkCount = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-unassign-idem'")->fetchColumn();
        $outboxCount = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-unassign-idem' AND topic='tag.unassigned'")->fetchColumn();
        $status = $pdo->query("SELECT status FROM idempotency_store WHERE tenant='tenant-unassign-idem' AND key='idem-unassign-1'")->fetchColumn();

        self::assertSame(['ok' => true, 'not_found' => false], $first);
        self::assertSame(['ok' => true, 'not_found' => false, 'duplicated' => true], $second);
        self::assertSame(0, $linkCount);
        self::assertSame(1, $outboxCount);
        self::assertSame('done', $status);
    }

    public function testUnassignWithSameIdempotencyKeyAndDifferentPayloadReturnsConflict(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-unassign-conflict', 'tenant-unassign-conflict', 'idem-conflict', 'Idem Conflict')");
        $pdo->exec("INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES ('tenant-unassign-conflict', 'product', 'p-1001', 'tag-unassign-conflict')");

        $service = new UnassignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));

        $first = $service->unassign('tenant-unassign-conflict', 'tag-unassign-conflict', 'product', 'p-1001', 'idem-unassign-conflict');
        $second = $service->unassign('tenant-unassign-conflict', 'tag-unassign-conflict', 'product', 'p-1002', 'idem-unassign-conflict');

        self::assertSame(['ok' => true, 'not_found' => false], $first);
        self::assertSame(['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'], $second);
    }
}
