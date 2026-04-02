<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignController;
use PHPUnit\Framework\TestCase;

final class TagBulkControllerBehaviorTest extends TestCase
{
    public function testBulkAggregatesMixedAssignAndUnassignResults(): void
    {
        $assign = new TagBulkAssignOperationStub([
            ['ok' => true],
            ['ok' => false, 'code' => 'tag_not_found'],
        ]);
        $unassign = new TagBulkUnassignOperationStub([
            ['ok' => true, 'not_found' => true],
        ]);

        $controller = new AssignController($assign, $unassign, ['entity_types' => ['product']]);
        [$status, , $body] = $controller->bulk([
            'headers' => ['X-Tenant-Id' => 'tenant-bulk'],
            'body' => [
                'operations' => [
                    ['op' => 'assign', 'tagId' => 'tag-1', 'entityType' => 'product', 'entityId' => 'p-1', 'idem' => 'idem-1'],
                    ['op' => 'assign', 'tagId' => 'tag-2', 'entityType' => 'product', 'entityId' => 'p-2', 'idem' => 'idem-2'],
                    ['op' => 'unassign', 'tagId' => 'tag-3', 'entityType' => 'product', 'entityId' => 'p-3', 'idem' => 'idem-3'],
                ],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertFalse($payload['ok']);
        self::assertSame(3, $payload['processed']);
        self::assertSame(2, $payload['done']);
        self::assertSame(1, $payload['errors']);
        self::assertCount(3, $payload['results']);
        self::assertSame('assign', $payload['results'][0]['op']);
        self::assertSame('assign', $payload['results'][1]['op']);
        self::assertSame('unassign', $payload['results'][2]['op']);
        self::assertSame('tag_not_found', $payload['results'][1]['code']);
        self::assertTrue($payload['results'][2]['not_found']);
    }

    public function testBulkMarksMalformedItemsAsValidationFailures(): void
    {
        $controller = new AssignController(new TagBulkAssignOperationStub([]), new TagBulkUnassignOperationStub([]), ['entity_types' => ['product']]);
        [$status, , $body] = $controller->bulk([
            'headers' => ['X-Tenant-Id' => 'tenant-bulk'],
            'body' => [
                'operations' => [
                    'not-an-array',
                    ['op' => 'assign', 'tagId' => '', 'entityType' => 'product', 'entityId' => 'p-1'],
                    ['op' => 'merge', 'tagId' => 'tag-2', 'entityType' => 'product', 'entityId' => 'p-2'],
                ],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertFalse($payload['ok']);
        self::assertSame(3, $payload['processed']);
        self::assertSame(0, $payload['done']);
        self::assertSame(3, $payload['errors']);
        self::assertSame('validation_failed', $payload['results'][0]['code']);
        self::assertSame('validation_failed', $payload['results'][1]['code']);
        self::assertSame('validation_failed', $payload['results'][2]['code']);
    }

    public function testBulkToEntityAggregatesAssignedDuplicatedAndErrors(): void
    {
        $assign = new TagBulkAssignOperationStub([
            ['ok' => true],
            ['ok' => true, 'duplicated' => true],
            ['ok' => false, 'code' => 'tag_not_found'],
        ]);
        $controller = new AssignController($assign, new TagBulkUnassignOperationStub([]), ['entity_types' => ['product']]);

        [$status, , $body] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'tenant-bulk'],
            'body' => [
                'entityType' => 'product',
                'entityId' => 'p-100',
                'tagIds' => ['tag-1', 'tag-2', 'tag-3'],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertFalse($payload['ok']);
        self::assertSame('product', $payload['entityType']);
        self::assertSame('p-100', $payload['entityId']);
        self::assertSame(3, $payload['processed']);
        self::assertSame(1, $payload['assigned']);
        self::assertSame(1, $payload['duplicated']);
        self::assertSame(1, $payload['errors']);
        self::assertSame('tag_not_found', $payload['items'][2]['code']);
    }

    public function testBulkEndpointsRejectInvalidTenantAndDisallowedEntityType(): void
    {
        $controller = new AssignController(new TagBulkAssignOperationStub([]), new TagBulkUnassignOperationStub([]), ['entity_types' => ['product']]);

        [$bulkStatus, , $bulkBody] = $controller->bulk([
            'headers' => [],
            'body' => ['operations' => []],
        ]);
        $bulkPayload = json_decode($bulkBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $bulkStatus);
        self::assertSame('invalid_tenant', $bulkPayload['code']);

        [$entityStatus, , $entityBody] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'tenant-bulk'],
            'body' => [
                'entityType' => 'project',
                'entityId' => 'proj-1',
                'tagIds' => ['tag-1'],
            ],
        ]);
        $entityPayload = json_decode($entityBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $entityStatus);
        self::assertSame('validation_failed', $entityPayload['code']);
    }
}

final class TagBulkAssignOperationStub implements \App\Service\Core\Tag\AssignOperationInterface
{
    /** @var list<array<string,mixed>> */
    private array $results;

    /** @param list<array<string,mixed>> $results */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function assign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        return array_shift($this->results) ?? ['ok' => true];
    }
}

final class TagBulkUnassignOperationStub implements \App\Service\Core\Tag\UnassignOperationInterface
{
    /** @var list<array<string,mixed>> */
    private array $results;

    /** @param list<array<string,mixed>> $results */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function unassign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        return array_shift($this->results) ?? ['ok' => true, 'not_found' => false];
    }
}
