<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

use App\Tagging\Entity\Core\Tag\TagLink;

final class TagUnassignTenantIsolationTest extends TagIntegrationEvidenceTestCase
{
    public function testUnassignRejectsCrossTenantTagAndKeepsWritesIsolated(): void
    {
        $this->insertTag('tenant-a', 'tag-a', 'tag-a', 'A');
        $this->insertTag('tenant-b', 'tag-b', 'tag-b', 'B');
        $this->entityManager()->persist(new TagLink('tenant-a', 'product', 'p-1', 'tag-a'));
        $this->entityManager()->persist(new TagLink('tenant-b', 'product', 'p-2', 'tag-b'));
        $this->entityManager()->flush();

        $service = $this->unassignService();

        $ok = $service->unassign('tenant-a', 'tag-a', 'product', 'p-1', 'idem-unassign-tenant-a-1');
        $crossTenant = $service->unassign('tenant-a', 'tag-b', 'product', 'p-2', 'idem-unassign-tenant-a-2');

        self::assertSame(['ok' => true, 'not_found' => false], $ok);
        self::assertSame(['ok' => false, 'code' => 'tag_not_found'], $crossTenant);
        self::assertSame(0, $this->countLinks('tenant-a'));
        self::assertSame(1, $this->countLinks('tenant-b'));
        self::assertSame(1, $this->countOutbox('tenant-a', 'tag.unassigned'));
        self::assertSame(0, $this->countOutbox('tenant-b', 'tag.unassigned'));
    }
}
