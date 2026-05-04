<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

final class TagAssignmentIdempotencyTest extends TagIntegrationEvidenceTestCase
{
    public function testAssignWithSameIdempotencyKeyIsStableOnRepeat(): void
    {
        $this->insertTag('tenant-idem', 'tag-idem', 'idem', 'Idem');
        $service = $this->assignService();
        $first = $service->assign('tenant-idem', 'tag-idem', 'product', 'p-1001', 'idem-key-1');
        $second = $service->assign('tenant-idem', 'tag-idem', 'product', 'p-1001', 'idem-key-1');

        self::assertSame(['ok' => true], $first);
        self::assertSame(['ok' => true, 'duplicated' => true], $second);
        self::assertSame(1, $this->countLinks('tenant-idem'));
        self::assertSame(1, $this->countOutbox('tenant-idem', 'tag.assigned'));
        self::assertSame('done', $this->idempotencyStatus('tenant-idem', 'idem-key-1'));
    }

    public function testAssignWithSameIdempotencyKeyAndDifferentPayloadReturnsConflict(): void
    {
        $this->insertTag('tenant-idem-conflict', 'tag-idem-conflict', 'idem-conflict', 'Idem Conflict');
        $service = $this->assignService();

        $first = $service->assign(
            'tenant-idem-conflict',
            'tag-idem-conflict',
            'product',
            'p-1001',
            'idem-key-conflict',
        );
        $second = $service->assign(
            'tenant-idem-conflict',
            'tag-idem-conflict',
            'product',
            'p-1002',
            'idem-key-conflict',
        );

        self::assertSame(['ok' => true], $first);
        self::assertSame(['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'], $second);
    }
}
