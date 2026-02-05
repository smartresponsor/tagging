<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Tag;

use App\Infra\Outbox\OutboxPublisher;
use App\Service\Tag\AssignService;
use App\Service\Tag\IdempotencyStore;
use PDO;
use PHPUnit\Framework\TestCase;

/** @group integration */
final class AssignFlowTest extends TestCase
{
    public function testAssignIsIdempotentByKey(): void
    {
        $dsn = getenv('DB_DSN') ?: '';
        if ($dsn === '') {
            $this->markTestSkipped('DB_DSN is not configured for integration test.');
        }

        $pdo = new PDO($dsn, getenv('DB_USER') ?: 'app', getenv('DB_PASS') ?: 'app');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tenant = 'demo';
        $tagId = '01HTESTASSIGN00000000000000';
        $entityType = 'product';
        $entityId = 'p-1001';
        $idemKey = 'idem-assign-flow-1';

        $pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('$tagId','$tenant','test-assign','Test Assign') ON CONFLICT DO NOTHING");

        $outbox = new OutboxPublisher($pdo);
        $idem = new IdempotencyStore($pdo);
        $assign = new AssignService($pdo, $outbox, $idem);

        $first = $assign->assign($tenant, $tagId, $entityType, $entityId, $idemKey);
        $second = $assign->assign($tenant, $tagId, $entityType, $entityId, $idemKey);

        $this->assertSame(['ok' => true], $first);
        $this->assertSame(['ok' => true, 'duplicated' => true], $second);
    }
}
