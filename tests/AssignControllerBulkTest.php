<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignController;
use App\Service\Core\Tag\AssignOperationInterface;
use App\Service\Core\Tag\UnassignOperationInterface;
use PHPUnit\Framework\TestCase;

final class AssignControllerBulkTest extends TestCase
{
    public function testBulkReturnsDetailedBatchResults(): void
    {
        $controller = new AssignController(
            new class() implements AssignOperationInterface {
                public function assign(
                    string $tenant,
                    string $tagId,
                    string $entityType,
                    string $entityId,
                    ?string $idemKey = null,
                ): array
                {
                    return ['ok' => true];
                }
            },
            new class() implements UnassignOperationInterface {
                public function unassign(
                    string $tenant,
                    string $tagId,
                    string $entityType,
                    string $entityId,
                    ?string $idemKey = null,
                ): array
                {
                    return ['ok' => false, 'code' => 'unassign_failed'];
                }
            },
            ['entity_types' => ['product']],
        );

        [$status, $headers, $body] = $controller->bulk([
            'headers' => ['X-Tenant-Id' => 'demo'],
            'body' => [
                'operations' => [
                    [
                        'op' => 'assign',
                        'tagId' => 'tag-a',
                        'entityType' => 'product',
                        'entityId' => 'p-1',
                        'idem' => 'op-1',
                    ],
                    [
                        'op' => 'unassign',
                        'tagId' => 'tag-b',
                        'entityType' => 'product',
                        'entityId' => 'p-2',
                        'idem' => 'op-2',
                    ],
                    ['op' => 'assign', 'tagId' => '', 'entityType' => 'product', 'entityId' => 'p-3'],
                ],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertSame('application/json', $headers['Content-Type']);
        self::assertFalse($payload['ok']);
        self::assertSame(3, $payload['processed']);
        self::assertSame(1, $payload['done']);
        self::assertSame(2, $payload['errors']);
        self::assertCount(3, $payload['results']);
        self::assertSame('assign', $payload['results'][0]['op']);
        self::assertSame('unassign_failed', $payload['results'][1]['code']);
        self::assertSame('validation_failed', $payload['results'][2]['code']);
    }

    public function testAssignBulkToEntityReturnsAssignmentCounters(): void
    {
        $controller = new AssignController(
            new class() implements AssignOperationInterface {
                public function assign(
                    string $tenant,
                    string $tagId,
                    string $entityType,
                    string $entityId,
                    ?string $idemKey = null,
                ): array
                {
                    return match ($tagId) {
                        'tag-a' => ['ok' => true],
                        'tag-b' => ['ok' => true, 'duplicated' => true],
                        default => ['ok' => false, 'code' => 'assign_failed'],
                    };
                }
            },
            new class() implements UnassignOperationInterface {
                public function unassign(
                    string $tenant,
                    string $tagId,
                    string $entityType,
                    string $entityId,
                    ?string $idemKey = null,
                ): array
                {
                    return ['ok' => true];
                }
            },
            ['entity_types' => ['product']],
        );

        [$status, , $body] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'demo'],
            'body' => [
                'entityType' => 'product',
                'entityId' => 'p-1',
                'tagIds' => ['tag-a', 'tag-b', ''],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertFalse($payload['ok']);
        self::assertSame('product', $payload['entityType']);
        self::assertSame('p-1', $payload['entityId']);
        self::assertSame(3, $payload['processed']);
        self::assertSame(1, $payload['assigned']);
        self::assertSame(1, $payload['duplicated']);
        self::assertSame(1, $payload['errors']);
        self::assertCount(3, $payload['items']);
        self::assertSame('tag-a', $payload['items'][0]['tagId']);
        self::assertTrue($payload['items'][1]['duplicated']);
        self::assertSame('validation_failed', $payload['items'][2]['code']);
    }
}
