<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

use App\Tagging\Entity\Core\Tag\TagLink;

final class TenantIsolationTest extends TagIntegrationEvidenceTestCase
{
    public function testAssignRejectsCrossTenantTagAndKeepsWritesIsolated(): void
    {
        $this->insertTag('tenant-a', 'tag-a', 'tag-a', 'A');
        $this->insertTag('tenant-b', 'tag-b', 'tag-b', 'B');
        $service = $this->assignService();
        $ok = $service->assign('tenant-a', 'tag-a', 'product', 'p-1', 'idem-tenant-a-1');
        $crossTenant = $service->assign('tenant-a', 'tag-b', 'product', 'p-2', 'idem-tenant-a-2');

        self::assertSame(['ok' => true], $ok);
        self::assertSame(['ok' => false, 'code' => 'tag_not_found'], $crossTenant);
        self::assertSame(1, $this->countLinks('tenant-a'));
        self::assertSame(0, $this->countLinks('tenant-b'));
        self::assertSame(1, $this->countOutbox('tenant-a', 'tag.assigned'));
        self::assertSame(0, $this->countOutbox('tenant-b', 'tag.assigned'));
    }
}
