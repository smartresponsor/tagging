<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\SearchController;
use App\Http\Api\Tag\SuggestController;
use PHPUnit\Framework\TestCase;

final class TagReadControllerTransportBehaviorTest extends TestCase
{
    public function testSearchUsesFlatPayloadAndNormalizesPagingInputs(): void
    {
        $service = new TagSearchServiceStub([
            'items' => [['id' => 'tag-1', 'slug' => 'alpha', 'name' => 'Alpha', 'locale' => 'en', 'weight' => 10]],
            'total' => 1,
            'nextPageToken' => 'next-1',
            'cacheHit' => true,
        ]);
        $controller = new SearchController($service);

        [$status, , $body] = $controller->get([
            'headers' => ['x-tenant-id' => ' tenant-read '],
            'query' => [
                'q' => ' alpha ',
                'pageSize' => '500',
                'pageToken' => ' next-token ',
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertTrue($payload['ok']);
        self::assertSame([['id' => 'tag-1', 'slug' => 'alpha', 'name' => 'Alpha', 'locale' => 'en', 'weight' => 10]], $payload['items']);
        self::assertSame(1, $payload['total']);
        self::assertSame('next-1', $payload['nextPageToken']);
        self::assertTrue($payload['cacheHit']);
        self::assertArrayNotHasKey('result', $payload);
        self::assertSame([
            ['tenant-read', 'alpha', 100, 'next-token'],
        ], $service->calls);
    }

    public function testSuggestUsesFlatPayloadAndClampsLimit(): void
    {
        $service = new TagSuggestServiceStub([
            'items' => [['slug' => 'alpha', 'name' => 'Alpha']],
            'cacheHit' => false,
        ]);
        $controller = new SuggestController($service);

        [$status, , $body] = $controller->get([
            'headers' => ['X-Tenant-Id' => 'tenant-read'],
            'query' => [
                'q' => ' al ',
                'limit' => '999',
            ],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertTrue($payload['ok']);
        self::assertSame([['slug' => 'alpha', 'name' => 'Alpha']], $payload['items']);
        self::assertFalse($payload['cacheHit']);
        self::assertArrayNotHasKey('result', $payload);
        self::assertSame([
            ['tenant-read', 'al', 50],
        ], $service->calls);
    }

    public function testReadControllersRejectMissingTenant(): void
    {
        $search = new SearchController(new TagSearchServiceStub(['items' => [], 'total' => 0, 'nextPageToken' => null, 'cacheHit' => false]));
        $suggest = new SuggestController(new TagSuggestServiceStub(['items' => [], 'cacheHit' => false]));

        [$searchStatus, , $searchBody] = $search->get(['headers' => [], 'query' => ['q' => 'alpha']]);
        [$suggestStatus, , $suggestBody] = $suggest->get(['headers' => [], 'query' => ['q' => 'al']]);

        $searchPayload = json_decode($searchBody, true, 512, JSON_THROW_ON_ERROR);
        $suggestPayload = json_decode($suggestBody, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $searchStatus);
        self::assertSame('invalid_tenant', $searchPayload['code']);
        self::assertSame(400, $suggestStatus);
        self::assertSame('invalid_tenant', $suggestPayload['code']);
    }
}

final class TagSearchServiceStub extends \App\Service\Core\Tag\SearchService
{
    /** @var array<string,mixed> */
    private array $result;

    /** @var list<array{0:string,1:string,2:int,3:?string}> */
    public array $calls = [];

    /** @param array<string,mixed> $result */
    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function search(string $tenant, string $query, int $pageSize = 20, ?string $pageToken = null): array
    {
        $this->calls[] = [$tenant, $query, $pageSize, $pageToken];

        return $this->result;
    }
}

final class TagSuggestServiceStub extends \App\Service\Core\Tag\SuggestService
{
    /** @var array<string,mixed> */
    private array $result;

    /** @var list<array{0:string,1:string,2:int}> */
    public array $calls = [];

    /** @param array<string,mixed> $result */
    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function suggest(string $tenant, string $query, int $limit = 10): array
    {
        $this->calls[] = [$tenant, $query, $limit];

        return $this->result;
    }
}
