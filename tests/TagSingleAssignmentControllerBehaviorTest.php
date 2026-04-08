<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignController;
use PHPUnit\Framework\TestCase;

final class TagSingleAssignmentControllerBehaviorTest extends TestCase
{
    public function testAssignMapsSuccessfulAndConflictResultsToExpectedTransportShape(): void
    {
        $assign = new TagSingleAssignOperationStub([
            ['ok' => true],
            ['ok' => false, 'conflict' => true, 'code' => 'idempotency_conflict'],
        ]);
        $controller = new AssignController(
            $assign,
            new TagSingleUnassignOperationStub([]),
            ['entity_types' => ['product']],
        );

        [$okStatus, , $okBody] = $controller->assign([
            'headers' => ['X-Tenant-Id' => 'tenant-single'],
            'body' => ['entityType' => 'product', 'entityId' => 'p-1', 'idem' => 'idem-1'],
        ], 'tag-1');
        [$conflictStatus, , $conflictBody] = $controller->assign([
            'headers' => ['X-Tenant-Id' => 'tenant-single'],
            'body' => ['entityType' => 'product', 'entityId' => 'p-2', 'idem' => 'idem-2'],
        ], 'tag-2');

        $okPayload = json_decode($okBody, true, 512, JSON_THROW_ON_ERROR);
        $conflictPayload = json_decode($conflictBody, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $okStatus);
        self::assertTrue($okPayload['ok']);
        self::assertFalse($okPayload['duplicated']);
        self::assertFalse($okPayload['conflict']);

        self::assertSame(409, $conflictStatus);
        self::assertFalse($conflictPayload['ok']);
        self::assertTrue($conflictPayload['conflict']);
        self::assertFalse($conflictPayload['duplicated']);
        self::assertSame('idempotency_conflict', $conflictPayload['code']);
    }

    public function testUnassignMapsNotFoundAndTagAbsenceSeparately(): void
    {
        $unassign = new TagSingleUnassignOperationStub([
            ['ok' => true, 'not_found' => true],
            ['ok' => false, 'code' => 'tag_not_found'],
        ]);
        $controller = new AssignController(
            new TagSingleAssignOperationStub([]),
            $unassign,
            ['entity_types' => ['product']],
        );

        [$okStatus, , $okBody] = $controller->unassign([
            'headers' => ['X-Tenant-Id' => 'tenant-single'],
            'body' => ['entityType' => 'product', 'entityId' => 'p-1', 'idem' => 'idem-1'],
        ], 'tag-1');
        [$missingStatus, , $missingBody] = $controller->unassign([
            'headers' => ['X-Tenant-Id' => 'tenant-single'],
            'body' => ['entityType' => 'product', 'entityId' => 'p-2', 'idem' => 'idem-2'],
        ], 'tag-2');

        $okPayload = json_decode($okBody, true, 512, JSON_THROW_ON_ERROR);
        $missingPayload = json_decode($missingBody, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $okStatus);
        self::assertTrue($okPayload['ok']);
        self::assertTrue($okPayload['not_found']);
        self::assertFalse($okPayload['duplicated']);
        self::assertFalse($okPayload['conflict']);

        self::assertSame(404, $missingStatus);
        self::assertFalse($missingPayload['ok']);
        self::assertFalse($missingPayload['not_found']);
        self::assertFalse($missingPayload['duplicated']);
        self::assertFalse($missingPayload['conflict']);
        self::assertSame('tag_not_found', $missingPayload['code']);
    }

    public function testSingleOperationsRejectInvalidTenantAndDisallowedEntityType(): void
    {
        $controller = new AssignController(
            new TagSingleAssignOperationStub([]),
            new TagSingleUnassignOperationStub([]),
            ['entity_types' => ['product']],
        );

        [$tenantStatus, , $tenantBody] = $controller->assign([
            'headers' => [],
            'body' => ['entityType' => 'product', 'entityId' => 'p-1'],
        ], 'tag-1');
        $tenantPayload = json_decode($tenantBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $tenantStatus);
        self::assertSame('invalid_tenant', $tenantPayload['code']);

        [$typeStatus, , $typeBody] = $controller->unassign([
            'headers' => ['X-Tenant-Id' => 'tenant-single'],
            'body' => ['entityType' => 'project', 'entityId' => 'proj-1'],
        ], 'tag-1');
        $typePayload = json_decode($typeBody, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $typeStatus);
        self::assertSame('validation_failed', $typePayload['code']);
    }
}

final class TagSingleAssignOperationStub implements \App\Service\Core\Tag\AssignOperationInterface
{
    /** @var list<array<string,mixed>> */
    private array $results;

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
        return array_shift($this->results) ?? ['ok' => true];
    }
}

final class TagSingleUnassignOperationStub implements \App\Service\Core\Tag\UnassignOperationInterface
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
