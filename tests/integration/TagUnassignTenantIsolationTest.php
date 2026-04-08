<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

require_once __DIR__ . '/IntegrationDbTestCase.php';

use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\IdempotencyStore;
use App\Service\Core\Tag\UnassignService;

final class TagUnassignTenantIsolationTest extends IntegrationDbTestCase
{
    public function testUnassignRejectsCrossTenantTagAndKeepsWritesIsolated(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-a', 'tenant-a', 'tag-a', 'A')");
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-b', 'tenant-b', 'tag-b', 'B')");
        $pdo->exec("INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES ('tenant-a', 'product', 'p-1', 'tag-a')");
        $pdo->exec("INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES ('tenant-b', 'product', 'p-2', 'tag-b')");

        $service = new UnassignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));

        $ok = $service->unassign('tenant-a', 'tag-a', 'product', 'p-1', 'idem-unassign-tenant-a-1');
        $crossTenant = $service->unassign('tenant-a', 'tag-b', 'product', 'p-2', 'idem-unassign-tenant-a-2');

        $tenantALinks = (int) $pdo
            ->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a'")
            ->fetchColumn();
        $tenantBLinks = (int) $pdo
            ->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-b'")
            ->fetchColumn();
        $tenantAOutbox = (int) $pdo
            ->query(
                "SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-a' AND topic='tag.unassigned'"
            )
            ->fetchColumn();
        $tenantBOutbox = (int) $pdo
            ->query(
                "SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-b' AND topic='tag.unassigned'"
            )
            ->fetchColumn();

        self::assertSame(['ok' => true, 'not_found' => false], $ok);
        self::assertSame(['ok' => false, 'code' => 'tag_not_found'], $crossTenant);
        self::assertSame(0, $tenantALinks);
        self::assertSame(1, $tenantBLinks);
        self::assertSame(1, $tenantAOutbox);
        self::assertSame(0, $tenantBOutbox);
    }
}
