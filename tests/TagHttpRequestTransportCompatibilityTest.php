<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignController;
use App\Http\Api\Tag\TagHttpRequest;
use PHPUnit\Framework\TestCase;

final class TagHttpRequestTransportCompatibilityTest extends TestCase
{
    public function testTenantHeaderAcceptsCanonicalAndLowercaseForms(): void
    {
        self::assertSame('tenant-a', TagHttpRequest::tenant([
            'headers' => ['X-Tenant-Id' => ' tenant-a '],
        ]));

        self::assertSame('tenant-b', TagHttpRequest::tenant([
            'headers' => ['x-tenant-id' => ' tenant-b '],
        ]));

        self::assertNull(TagHttpRequest::tenantOrNull([
            'headers' => ['x-tenant-id' => '   '],
        ]));
    }

    public function testQueryAndBodyHelpersSupportFallbackKeysAndBounds(): void
    {
        $request = [
            'query' => ['page_token' => ' token-1 ', 'pageSize' => '999'],
            'body' => ['entity_id' => ' p-1 '],
        ];

        self::assertSame('token-1', TagHttpRequest::queryString($request, 'pageToken', 'page_token'));
        self::assertSame(50, TagHttpRequest::queryInt($request, 'pageSize', 20, 1, 50));
        self::assertSame('p-1', TagHttpRequest::bodyString($request, 'entityId', 'entity_id'));
    }

    public function testControllerAcceptsLowercaseTenantHeaderForBulkAndSingleOperations(): void
    {
        $assign = new TagRequestCompatAssignStub([
            ['ok' => true],
            ['ok' => true],
        ]);
        $controller = new AssignController($assign, new TagRequestCompatUnassignStub([]), ['entity_types' => ['product']]);

        [$singleStatus, , $singleBody] = $controller->assign([
            'headers' => ['x-tenant-id' => 'tenant-lower'],
            'body' => ['entityType' => 'product', 'entityId' => 'p-1'],
        ], 'tag-1');
        [$bulkStatus, , $bulkBody] = $controller->bulk([
            'headers' => ['x-tenant-id' => 'tenant-lower'],
            'body' => [
                'operations' => [
                    ['op' => 'assign', 'tagId' => 'tag-2', 'entityType' => 'product', 'entityId' => 'p-2'],
                ],
            ],
        ]);

        $singlePayload = json_decode($singleBody, true, 512, JSON_THROW_ON_ERROR);
        $bulkPayload = json_decode($bulkBody, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $singleStatus);
        self::assertTrue($singlePayload['ok']);
        self::assertSame(200, $bulkStatus);
        self::assertTrue($bulkPayload['ok']);
        self::assertSame([
            ['tenant-lower', 'tag-1', 'product', 'p-1', null],
            ['tenant-lower', 'tag-2', 'product', 'p-2', null],
        ], $assign->calls);
    }
}

final class TagRequestCompatAssignStub implements \App\Service\Core\Tag\AssignOperationInterface
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

    public function assign(string $tenant, string $tagId, string $entityType, string $entityId, ?string $idemKey = null): array
    {
        $this->calls[] = [$tenant, $tagId, $entityType, $entityId, $idemKey];

        return array_shift($this->results) ?? ['ok' => true];
    }
}

final class TagRequestCompatUnassignStub implements \App\Service\Core\Tag\UnassignOperationInterface
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
