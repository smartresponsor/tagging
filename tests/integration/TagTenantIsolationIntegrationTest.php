<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace Tests\Integration;

final class TagTenantIsolationIntegrationTest extends TagIntegrationEvidenceTestCase
{
    public function testSearchAndAssignmentReadsRemainTenantIsolatedOnSharedEntityCoordinates(): void
    {
        $pdo = $this->pdo();
        $tenantA = 'tenant-alpha';
        $tenantB = 'tenant-beta';
        $tagA = '01K3TAGDEMO00000000001001';
        $tagB = '01K3TAGDEMO00000000001002';

        $this->insertTag($pdo, $tenantA, $tagA, 'alpha-electronics', 'Alpha Electronics', 100);
        $this->insertTag($pdo, $tenantB, $tagB, 'beta-electronics', 'Beta Electronics', 90);

        $assign = $this->assignService($pdo);
        self::assertTrue($assign->assign($tenantA, $tagA, 'product', 'shared-product-1')['ok'] ?? false);
        self::assertTrue($assign->assign($tenantB, $tagB, 'product', 'shared-product-1')['ok'] ?? false);

        $read = $this->readModel($pdo);

        $alphaSearch = $read->search($tenantA, 'electronics', 10, 0);
        $betaSearch = $read->search($tenantB, 'electronics', 10, 0);
        self::assertCount(1, $alphaSearch);
        self::assertCount(1, $betaSearch);
        self::assertSame($tagA, $alphaSearch[0]['id']);
        self::assertSame($tagB, $betaSearch[0]['id']);
        self::assertSame(1, $read->countSearch($tenantA, 'electronics'));
        self::assertSame(1, $read->countSearch($tenantB, 'electronics'));

        $alphaEntityTags = $read->tagsForEntity($tenantA, 'product', 'shared-product-1', 10);
        $betaEntityTags = $read->tagsForEntity($tenantB, 'product', 'shared-product-1', 10);
        self::assertCount(1, $alphaEntityTags);
        self::assertCount(1, $betaEntityTags);
        self::assertSame($tagA, $alphaEntityTags[0]['id']);
        self::assertSame($tagB, $betaEntityTags[0]['id']);
    }
}
