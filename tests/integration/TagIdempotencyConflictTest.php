<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

final class TagIdempotencyConflictTest extends TagIntegrationEvidenceTestCase
{
    public function testAssignRejectsSameIdempotencyKeyWithDifferentPayload(): void
    {
        $this->insertTag('tenant-idem-conflict', 'tag-idem-conflict', 'idem-conflict', 'Idem Conflict');
        $service = $this->assignService();

        $first = $service->assign(
            'tenant-idem-conflict',
            'tag-idem-conflict',
            'product',
            'p-2001',
            'idem-conflict-key-1',
        );
        $conflict = $service->assign(
            'tenant-idem-conflict',
            'tag-idem-conflict',
            'product',
            'p-2002',
            'idem-conflict-key-1',
        );

        self::assertSame(['ok' => true], $first);
        self::assertSame(['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'], $conflict);
        self::assertSame(1, $this->countLinks('tenant-idem-conflict'));
        self::assertSame(1, $this->countOutbox('tenant-idem-conflict', 'tag.assigned'));
    }
}
