<?php

declare(strict_types=1);

namespace Tests\Integration;

final class TagWriteSymmetryIntegrationTest extends TagIntegrationEvidenceTestCase
{
    public function testSingleAssignAndUnassignPreserveIdempotencyAndMissingTagSemantics(): void
    {
        $pdo = $this->pdo();
        $tenant = 'tenant-symmetry';
        $tagId = '01K3TAGDEMO00000000002001';

        $this->insertTag($pdo, $tenant, $tagId, 'symmetry-primary', 'Symmetry Primary', 80);

        $assign = $this->assignService($pdo);
        $unassign = $this->unassignService($pdo);

        $firstAssign = $assign->assign($tenant, $tagId, 'collection', 'symmetry-entity-1', 'idem-assign-1');
        self::assertTrue($firstAssign['ok'] ?? false);
        self::assertArrayNotHasKey('duplicated', $firstAssign);

        $duplicateAssign = $assign->assign($tenant, $tagId, 'collection', 'symmetry-entity-1', 'idem-assign-1');
        self::assertTrue($duplicateAssign['ok'] ?? false);
        self::assertTrue($duplicateAssign['duplicated'] ?? false);

        $firstUnassign = $unassign->unassign($tenant, $tagId, 'collection', 'symmetry-entity-1', 'idem-unassign-1');
        self::assertTrue($firstUnassign['ok'] ?? false);
        self::assertFalse($firstUnassign['not_found'] ?? true);

        $duplicateUnassign = $unassign->unassign($tenant, $tagId, 'collection', 'symmetry-entity-1', 'idem-unassign-1');
        self::assertTrue($duplicateUnassign['ok'] ?? false);
        self::assertTrue($duplicateUnassign['duplicated'] ?? false);
        self::assertFalse($duplicateUnassign['not_found'] ?? true);

        $linkAlreadyAbsent = $unassign->unassign($tenant, $tagId, 'collection', 'symmetry-entity-1', 'idem-unassign-2');
        self::assertTrue($linkAlreadyAbsent['ok'] ?? false);
        self::assertTrue($linkAlreadyAbsent['not_found'] ?? false);

        $missingTag = $unassign->unassign($tenant, '01HMISSINGTAG0000000000000', 'collection', 'symmetry-entity-1', 'idem-missing-1');
        self::assertFalse($missingTag['ok'] ?? true);
        self::assertSame('tag_not_found', $missingTag['code'] ?? null);
    }

    public function testBulkControllerFlowsStaySymmetricWithEntityReads(): void
    {
        $pdo = $this->pdo();
        $tenant = 'tenant-bulk';
        $tagA = '01K3TAGDEMO00000000002011';
        $tagB = '01K3TAGDEMO00000000002012';

        $this->insertTag($pdo, $tenant, $tagA, 'bulk-primary', 'Bulk Primary', 100);
        $this->insertTag($pdo, $tenant, $tagB, 'bulk-secondary', 'Bulk Secondary', 50);

        $controller = $this->assignController($pdo);
        $read = $this->readModel($pdo);

        [$status, , $body] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => $tenant],
            'body' => [
                'entityType' => 'bundle',
                'entityId' => 'bundle-entity-1',
                'tagIds' => [$tagA, $tagB],
            ],
        ]);
        self::assertSame(200, $status);
        $payload = $this->decodeBody($body);
        self::assertTrue($payload['ok'] ?? false);
        self::assertSame(2, $payload['processed'] ?? null);
        self::assertSame(0, $payload['errors'] ?? null);

        $before = $read->tagsForEntity($tenant, 'bundle', 'bundle-entity-1', 10);
        self::assertCount(2, $before);

        [$bulkStatus, , $bulkBody] = $controller->bulk([
            'headers' => ['X-Tenant-Id' => $tenant],
            'body' => [
                'operations' => [
                    [
                        'op' => 'unassign',
                        'tagId' => $tagA,
                        'entityType' => 'bundle',
                        'entityId' => 'bundle-entity-1',
                        'idem' => 'bulk-unassign-1',
                    ],
                    [
                        'op' => 'assign',
                        'tagId' => $tagB,
                        'entityType' => 'bundle',
                        'entityId' => 'bundle-entity-1',
                        'idem' => 'bulk-assign-duplicate-1',
                    ],
                ],
            ],
        ]);
        self::assertSame(200, $bulkStatus);
        $bulkPayload = $this->decodeBody($bulkBody);
        self::assertTrue($bulkPayload['ok'] ?? false);
        self::assertSame(2, $bulkPayload['processed'] ?? null);
        self::assertSame(0, $bulkPayload['errors'] ?? null);
        self::assertCount(2, $bulkPayload['results'] ?? []);

        $after = $read->tagsForEntity($tenant, 'bundle', 'bundle-entity-1', 10);
        self::assertCount(1, $after);
        self::assertSame($tagB, $after[0]['id']);
    }
}
