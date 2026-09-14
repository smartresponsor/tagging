<?php

declare(strict_types=1);

namespace Tests\Integration;

final class TagWriteSymmetryIntegrationTest extends TagIntegrationEvidenceTestCase
{
    public function testSingleAssignAndUnassignPreserveIdempotencyAndMissingTagSemantics(): void
    {
        $tenant = 'tenant-symmetry';
        $tagId = '01K3TAGDEMO00000000002001';

        $this->insertTag($tenant, $tagId, 'symmetry-primary', 'Symmetry Primary', 80);

        $assign = $this->assignService();
        $unassign = $this->unassignService();

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

    public function testWriteServicesStaySymmetricWithEntityReads(): void
    {
        $tenant = 'tenant-bulk';
        $tagA = '01K3TAGDEMO00000000002011';
        $tagB = '01K3TAGDEMO00000000002012';
        $entityType = 'bundle';
        $entityId = 'bundle-entity-1';

        $this->insertTag($tenant, $tagA, 'bulk-primary', 'Bulk Primary', 100);
        $this->insertTag($tenant, $tagB, 'bulk-secondary', 'Bulk Secondary', 50);

        $assign = $this->assignService();
        $unassign = $this->unassignService();
        $read = $this->readModel();

        self::assertTrue($assign->assign($tenant, $tagA, $entityType, $entityId, 'bulk-assign-a')['ok'] ?? false);
        self::assertTrue($assign->assign($tenant, $tagB, $entityType, $entityId, 'bulk-assign-b')['ok'] ?? false);
        self::assertCount(2, $read->tagsForEntity($tenant, $entityType, $entityId, 10));

        self::assertTrue($unassign->unassign($tenant, $tagA, $entityType, $entityId, 'bulk-unassign-a')['ok'] ?? false);
        $duplicate = $assign->assign($tenant, $tagB, $entityType, $entityId, 'bulk-assign-b-duplicate');
        self::assertTrue($duplicate['ok'] ?? false);
        self::assertTrue($duplicate['duplicated'] ?? false);

        $after = $read->tagsForEntity($tenant, $entityType, $entityId, 10);
        self::assertCount(1, $after);
        self::assertSame($tagB, $after[0]['id']);
    }
}
