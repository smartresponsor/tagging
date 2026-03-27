<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

require_once __DIR__ . '/IntegrationDbTestCase.php';

use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\AssignService;
use App\Service\Core\Tag\IdempotencyStore;

final class TenantIsolationTest extends IntegrationDbTestCase
{
    public function testAssignRejectsCrossTenantTagAndKeepsWritesIsolated(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-a', 'tenant-a', 'tag-a', 'A')");
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-b', 'tenant-b', 'tag-b', 'B')");
        $service = new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo));
        $ok = $service->assign('tenant-a', 'tag-a', 'product', 'p-1', 'idem-tenant-a-1');
        $crossTenant = $service->assign('tenant-a', 'tag-b', 'product', 'p-2', 'idem-tenant-a-2');
        $tenantALinks = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a'")->fetchColumn();
        $tenantBLinks = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-b'")->fetchColumn();
        $tenantAOutbox = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-a' AND topic='tag.assigned'")->fetchColumn();
        $tenantBOutbox = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-b' AND topic='tag.assigned'")->fetchColumn();

        self::assertSame(['ok' => true], $ok);
        self::assertSame(['ok' => false, 'code' => 'tag_not_found'], $crossTenant);
        self::assertSame(1, $tenantALinks);
        self::assertSame(0, $tenantBLinks);
        self::assertSame(1, $tenantAOutbox);
        self::assertSame(0, $tenantBOutbox);
    }
}
