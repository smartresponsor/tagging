<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignController;
use PHPUnit\Framework\TestCase;

final class TagBulkControllerPayloadCompatibilityTest extends TestCase
{
    public function testBulkAcceptsSnakeCaseEntityCoordinatesAndTagIdAliases(): void
    {
        $assign = new TagBulkCompatAssignOperationStub([
            ['ok' => true],
            ['ok' => true, 'duplicated' => true],
        ]);
        $controller = new AssignController(
            $assign,
            new TagBulkCompatUnassignOperationStub([]),
            ['entity_types' => ['product']],
        );

        [$status, , $body] = $controller->bulk([
            'headers' => ['X-Tenant-Id' => 'tenant-compat'],
            'body' => [
                'operations' => [
                    [
                        'op' => 'assign',
                        'tag_id' => 'tag-1',
                        'entity_type' => 'product',
                        'entity_id' => 'p-1',
                        'idemKey' => 'idem-1',
                    ],
                    [
                        'op' => 'assign',
                        'tagId' => 'tag-2',
                        'entityType' => 'product',
                        'entityId' => 'p-2',
                        'idem' => 'idem-2',
                    ],
                ],
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertTrue($payload['results'][0]['ok']);
        self::assertTrue($payload['results'][1]['ok']);
        self::assertTrue($payload['results'][1]['duplicated']);

        self::assertSame([
            ['tenant-compat', 'tag-1', 'product', 'p-1', 'idem-1'],
            ['tenant-compat', 'tag-2', 'product', 'p-2', 'idem-2'],
        ], $assign->calls);
    }

    public function testAssignBulkToEntityAcceptsTagIdsAndTagIdsSnakeCaseAliases(): void
    {
        $assign = new TagBulkCompatAssignOperationStub([
            ['ok' => true],
            ['ok' => true],
        ]);
        $controller = new AssignController(
            $assign,
            new TagBulkCompatUnassignOperationStub([]),
            ['entity_types' => ['*']],
        );

        [$statusA, , $bodyA] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'tenant-compat'],
            'body' => [
                'entityType' => 'project',
                'entityId' => 'proj-1',
                'tagIds' => ['tag-a'],
            ],
        ]);
        [$statusB, , $bodyB] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'tenant-compat'],
            'body' => [
                'entity_type' => 'project',
                'entity_id' => 'proj-2',
                'tag_ids' => ['tag-b'],
            ],
        ]);

        $payloadA = json_decode($bodyA, true, 512, JSON_THROW_ON_ERROR);
        $payloadB = json_decode($bodyB, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $statusA);
        self::assertSame(200, $statusB);
        self::assertSame('proj-1', $payloadA['entityId']);
        self::assertSame('proj-2', $payloadB['entityId']);
        self::assertSame([
            ['tenant-compat', 'tag-a', 'project', 'proj-1', null],
            ['tenant-compat', 'tag-b', 'project', 'proj-2', null],
        ], $assign->calls);
    }

    public function testAssignBulkToEntityRejectsEmptyTagListAndBlankTagItems(): void
    {
        $controller = new AssignController(
            new TagBulkCompatAssignOperationStub([]),
            new TagBulkCompatUnassignOperationStub([]),
            ['entity_types' => ['product']],
        );

        [$emptyStatus, , $emptyBody] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'tenant-compat'],
            'body' => [
                'entityType' => 'product',
                'entityId' => 'p-1',
                'tagIds' => [],
            ],
        ]);
        $emptyPayload = json_decode($emptyBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $emptyStatus);
        self::assertSame('validation_failed', $emptyPayload['code']);

        [$blankStatus, , $blankBody] = $controller->assignBulkToEntity([
            'headers' => ['X-Tenant-Id' => 'tenant-compat'],
            'body' => [
                'entityType' => 'product',
                'entityId' => 'p-2',
                'tagIds' => ['   ', 'tag-ok'],
            ],
        ]);
        $blankPayload = json_decode($blankBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(200, $blankStatus);
        self::assertFalse($blankPayload['ok']);
        self::assertSame(2, $blankPayload['processed']);
        self::assertSame(1, $blankPayload['assigned']);
        self::assertSame(1, $blankPayload['errors']);
        self::assertSame('validation_failed', $blankPayload['items'][0]['code']);
        self::assertTrue($blankPayload['items'][1]['ok']);
    }
}

final class TagBulkCompatAssignOperationStub implements \App\Service\Core\Tag\AssignOperationInterface
{
    /** @var list<array<string,mixed>> */
    private array $results;

    /** @var list<array{0:string,1:string,2:string,3:string,4:?string}> */
    public array $calls = [];

    /** @param list<array<string,mixed>> $results */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function assign(
        string $tenant,
        string $tagId,
        string $entityType,
        string $entityId,
        ?string $idemKey = null,
    ): array
    {
        $this->calls[] = [$tenant, $tagId, $entityType, $entityId, $idemKey];

        return array_shift($this->results) ?? ['ok' => true];
    }
}

final class TagBulkCompatUnassignOperationStub implements \App\Service\Core\Tag\UnassignOperationInterface
{
    /** @var list<array<string,mixed>> */
    private array $results;

    /** @param list<array<string,mixed>> $results */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function unassign(
        string $tenant,
        string $tagId,
        string $entityType,
        string $entityId,
        ?string $idemKey = null,
    ): array
    {
        return array_shift($this->results) ?? ['ok' => true, 'not_found' => false];
    }
}
