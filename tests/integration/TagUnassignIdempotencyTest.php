<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

use App\Tagging\Entity\Core\Tag\TagLink;

final class TagUnassignIdempotencyTest extends TagIntegrationEvidenceTestCase
{
    public function testUnassignWithSameIdempotencyKeyIsStableOnRepeat(): void
    {
        $this->insertTag('tenant-unassign-idem', 'tag-unassign-idem', 'idem', 'Idem');
        $this->entityManager()->persist(new TagLink('tenant-unassign-idem', 'product', 'p-1001', 'tag-unassign-idem'));
        $this->entityManager()->flush();

        $service = $this->unassignService();

        $first = $service->unassign(
            'tenant-unassign-idem',
            'tag-unassign-idem',
            'product',
            'p-1001',
            'idem-unassign-1',
        );
        $second = $service->unassign(
            'tenant-unassign-idem',
            'tag-unassign-idem',
            'product',
            'p-1001',
            'idem-unassign-1',
        );

        self::assertSame(['ok' => true, 'not_found' => false], $first);
        self::assertSame(['ok' => true, 'not_found' => false, 'duplicated' => true], $second);
        self::assertSame(0, $this->countLinks('tenant-unassign-idem'));
        self::assertSame(1, $this->countOutbox('tenant-unassign-idem', 'tag.unassigned'));
        self::assertSame('done', $this->idempotencyStatus('tenant-unassign-idem', 'idem-unassign-1'));
    }

    public function testUnassignWithSameIdempotencyKeyAndDifferentPayloadReturnsConflict(): void
    {
        $this->insertTag('tenant-unassign-conflict', 'tag-unassign-conflict', 'idem-conflict', 'Idem Conflict');
        $this->entityManager()->persist(new TagLink('tenant-unassign-conflict', 'product', 'p-1001', 'tag-unassign-conflict'));
        $this->entityManager()->flush();

        $service = $this->unassignService();

        $first = $service->unassign(
            'tenant-unassign-conflict',
            'tag-unassign-conflict',
            'product',
            'p-1001',
            'idem-unassign-conflict',
        );
        $second = $service->unassign(
            'tenant-unassign-conflict',
            'tag-unassign-conflict',
            'product',
            'p-1002',
            'idem-unassign-conflict',
        );

        self::assertSame(['ok' => true, 'not_found' => false], $first);
        self::assertSame(['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'], $second);
    }
}
