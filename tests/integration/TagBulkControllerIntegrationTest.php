<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

require_once __DIR__ . '/IntegrationDbTestCase.php';

use App\Http\Api\Tag\AssignController;
use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Service\Core\Tag\AssignService;
use App\Service\Core\Tag\IdempotencyStore;
use App\Service\Core\Tag\UnassignService;

final class TagBulkControllerIntegrationTest extends IntegrationDbTestCase
{
    public function testBulkPersistsMixedAssignAndUnassignResultsAgainstDatabase(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-a', 'tenant-a', 'tag-a', 'A')");
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-b', 'tenant-a', 'tag-b', 'B')");
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-c', 'tenant-b', 'tag-c', 'C')");
        $pdo->exec("INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES ('tenant-a', 'product', 'p-2', 'tag-b')");

        $controller = new AssignController(
            new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo)),
            new UnassignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo)),
            ['entity_types' => ['product']]
        );

        [$status, , $body] = $controller->bulk([
            'headers' => ['X-Tenant-Id' => 'tenant-a'],
            'body' => [
                'operations' => [
                    ['op' => 'assign', 'tagId' => 'tag-a', 'entityType' => 'product', 'entityId' => 'p-1', 'idem' => 'bulk-1'],
                    ['op' => 'unassign', 'tagId' => 'tag-b', 'entityType' => 'product', 'entityId' => 'p-2', 'idem' => 'bulk-2'],
                    ['op' => 'assign', 'tagId' => 'tag-c', 'entityType' => 'product', 'entityId' => 'p-3', 'idem' => 'bulk-3'],
                ],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $tenantALinks = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a'")->fetchColumn();
        $assignedOutbox = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-a' AND topic='tag.assigned'")->fetchColumn();
        $unassignedOutbox = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-a' AND topic='tag.unassigned'")->fetchColumn();
        $idempotency = (int) $pdo->query("SELECT COUNT(*) FROM idempotency_store WHERE tenant='tenant-a'")->fetchColumn();
        $p1Link = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a' AND entity_id='p-1' AND tag_id='tag-a'")->fetchColumn();
        $p2Link = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a' AND entity_id='p-2' AND tag_id='tag-b'")->fetchColumn();

        self::assertSame(200, $status);
        self::assertFalse($payload['ok']);
        self::assertSame(3, $payload['processed']);
        self::assertSame(2, $payload['done']);
        self::assertSame(1, $payload['errors']);
        self::assertSame('tag_not_found', $payload['results'][2]['code'] ?? null);
        self::assertSame(1, $tenantALinks);
        self::assertSame(1, $assignedOutbox);
        self::assertSame(1, $unassignedOutbox);
        self::assertSame(3, $idempotency);
        self::assertSame(1, $p1Link);
        self::assertSame(0, $p2Link);
    }

    public function testAssignBulkToEntityPersistsAssignedDuplicatedAndErrorResults(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-1', 'tenant-a', 'tag-1', 'One')");
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-2', 'tenant-a', 'tag-2', 'Two')");
        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('tag-3', 'tenant-b', 'tag-3', 'Three')");
        $pdo->exec("INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES ('tenant-a', 'product', 'p-100', 'tag-2')");

        $controller = new AssignController(
            new AssignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo)),
            new UnassignService($pdo, new OutboxPublisher($pdo), new IdempotencyStore($pdo)),
            ['entity_types' => ['product']]
        );

        [$status, , $body] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'tenant-a'],
            'body' => [
                'entityType' => 'product',
                'entityId' => 'p-100',
                'tagIds' => ['tag-1', 'tag-2', 'tag-3'],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $tenantALinks = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a' AND entity_id='p-100'")->fetchColumn();
        $assignedOutbox = (int) $pdo->query("SELECT COUNT(*) FROM outbox_event WHERE tenant='tenant-a' AND topic='tag.assigned'")->fetchColumn();
        $tag1Link = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a' AND entity_id='p-100' AND tag_id='tag-1'")->fetchColumn();
        $tag2Link = (int) $pdo->query("SELECT COUNT(*) FROM tag_link WHERE tenant='tenant-a' AND entity_id='p-100' AND tag_id='tag-2'")->fetchColumn();

        self::assertSame(200, $status);
        self::assertFalse($payload['ok']);
        self::assertSame('product', $payload['entityType']);
        self::assertSame('p-100', $payload['entityId']);
        self::assertSame(3, $payload['processed']);
        self::assertSame(1, $payload['assigned']);
        self::assertSame(1, $payload['duplicated']);
        self::assertSame(1, $payload['errors']);
        self::assertSame('tag_not_found', $payload['items'][2]['code'] ?? null);
        self::assertSame(2, $tenantALinks);
        self::assertSame(1, $assignedOutbox);
        self::assertSame(1, $tag1Link);
        self::assertSame(1, $tag2Link);
    }
}
