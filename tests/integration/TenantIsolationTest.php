<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

use App\Infra\Outbox\OutboxPublisher;
use App\Service\Tag\AssignService;
use App\Service\Tag\IdempotencyStore;

final class TenantIsolationTest extends IntegrationDbTestCase
{
    public function testAssignRejectsCrossTenantTagAndKeepsWritesIsolated(): void
    {
        $pdo = self::pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-a', 'tenant-a', 'a', 'A')");
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-b', 'tenant-b', 'b', 'B')");

        $service = new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));

        $ok = $service->assign('tenant-a', 'tag-a', 'product', 'p-1', 'idem-tenant-a-1');
        $crossTenant = $service->assign('tenant-a', 'tag-b', 'product', 'p-2', 'idem-tenant-a-2');

        $tenantALinks = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a'")->fetchColumn();
        $tenantBLinks = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-b'")->fetchColumn();
        $tenantAOutbox = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-a' AND topic='tag.assigned'")->fetchColumn();
        $tenantBOutbox = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-b' AND topic='tag.assigned'")->fetchColumn();

        $this->assertSame(['ok' => true], $ok);
        $this->assertSame(['ok' => false], $crossTenant);
        $this->assertSame(1, $tenantALinks);
        $this->assertSame(0, $tenantBLinks);
        $this->assertSame(1, $tenantAOutbox);
        $this->assertSame(0, $tenantBOutbox);
    }
}
