<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

use App\Infra\Outbox\OutboxPublisher;
use App\Service\Tag\AssignService;
use App\Service\Tag\IdempotencyStore;

final class TagAssignmentIdempotencyTest extends IntegrationDbTestCase
{
    public function testAssignWithSameIdempotencyKeyIsStableOnRepeat(): void
    {
        $pdo = self::pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-idem', 'tenant-idem', 'idem', 'Idem')");

        $service = new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));

        $first = $service->assign('tenant-idem', 'tag-idem', 'product', 'p-1001', 'idem-key-1');
        $second = $service->assign('tenant-idem', 'tag-idem', 'product', 'p-1001', 'idem-key-1');

        $linkCount = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-idem'")->fetchColumn();
        $outboxCount = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-idem' AND topic='tag.assigned'")->fetchColumn();
        $status = $pdo->query("SELECT status FROM idempotency_store WHERE tenant='tenant-idem' AND key='idem-key-1'")->fetchColumn();

        $this->assertSame(['ok' => true], $first);
        $this->assertSame(['ok' => true, 'duplicated' => true], $second);
        $this->assertSame(1, $linkCount);
        $this->assertSame(1, $outboxCount);
        $this->assertSame('done', $status);
    }
}
