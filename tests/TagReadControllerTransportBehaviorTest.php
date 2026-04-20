<?php

declare(strict_types=1);

namespace Tests;

use App\Tagging\Cache\Store\Tag\SearchCache;
use App\Tagging\Cache\Store\Tag\SuggestCache;
use App\Tagging\Http\Api\Tag\SearchController;
use App\Tagging\Http\Api\Tag\SuggestController;
use App\Tagging\Service\Core\Tag\SearchService;
use App\Tagging\Service\Core\Tag\SuggestService;
use App\Tagging\Service\Core\Tag\TagReadModelInterface;
use PHPUnit\Framework\TestCase;

final class TagReadControllerTransportBehaviorTest extends TestCase
{
    public function testSearchUsesFlatPayloadAndNormalizesPagingInputs(): void
    {
        $read = new RecordingTagReadModel(
            searchItems: [['id' => 'tag-1', 'slug' => 'alpha', 'name' => 'Alpha', 'locale' => 'en', 'weight' => 10]],
            searchTotal: 1,
        );
        $service = new SearchService($read, new SearchCache($this->cacheDir('search')));
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
        self::assertNull($payload['nextPageToken']);
        self::assertFalse($payload['cacheHit']);
        self::assertArrayNotHasKey('result', $payload);
        self::assertSame([['tenant-read', 'alpha', 100, 0]], $read->searchCalls);
        self::assertSame([['tenant-read', 'alpha']], $read->countCalls);
    }

    public function testSuggestUsesFlatPayloadAndClampsLimit(): void
    {
        $read = new RecordingTagReadModel(
            suggestItems: [['slug' => 'alpha', 'name' => 'Alpha']],
        );
        $service = new SuggestService($read, new SuggestCache($this->cacheDir('suggest')));
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
        self::assertSame([['tenant-read', 'al', 50]], $read->suggestCalls);
    }

    public function testReadControllersRejectMissingTenant(): void
    {
        $search = new SearchController(new SearchService(new RecordingTagReadModel(), new SearchCache($this->cacheDir('search-missing-tenant'))));
        $suggest = new SuggestController(new SuggestService(new RecordingTagReadModel(), new SuggestCache($this->cacheDir('suggest-missing-tenant'))));

        [$searchStatus, , $searchBody] = $search->get(['headers' => [], 'query' => ['q' => 'alpha']]);
        [$suggestStatus, , $suggestBody] = $suggest->get(['headers' => [], 'query' => ['q' => 'al']]);

        $searchPayload = json_decode($searchBody, true, 512, JSON_THROW_ON_ERROR);
        $suggestPayload = json_decode($suggestBody, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $searchStatus);
        self::assertSame('invalid_tenant', $searchPayload['code']);
        self::assertSame(400, $suggestStatus);
        self::assertSame('invalid_tenant', $suggestPayload['code']);
    }

    private function cacheDir(string $suffix): string
    {
        return sys_get_temp_dir() . '/smartresponsor-tagging-' . $suffix . '-' . bin2hex(random_bytes(4));
    }
}

final class RecordingTagReadModel implements TagReadModelInterface
{
    /** @param array<int, array{id:string,slug:string,name:string,locale:?string,weight:int}> $searchItems */
    /** @param array<int, array{slug:string,name:string}> $suggestItems */
    public function __construct(
        private array $searchItems = [],
        private int $searchTotal = 0,
        private array $suggestItems = [],
    ) {}

    /** @var list<array{0:string,1:string,2:int,3:int}> */
    public array $searchCalls = [];

    /** @var list<array{0:string,1:string}> */
    public array $countCalls = [];

    /** @var list<array{0:string,1:string,2:int}> */
    public array $suggestCalls = [];

    public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
    {
        $this->searchCalls[] = [$tenant, $q, $limit, $offset];

        return $this->searchItems;
    }

    public function countSearch(string $tenant, string $q): int
    {
        $this->countCalls[] = [$tenant, $q];

        return $this->searchTotal;
    }

    public function suggest(string $tenant, string $q, int $limit = 10): array
    {
        $this->suggestCalls[] = [$tenant, $q, $limit];

        return $this->suggestItems;
    }

    public function linksForTag(string $tenant, string $tagId, int $limit = 100): array
    {
        return [];
    }

    public function tagsForEntity(string $tenant, string $etype, string $eid, int $limit = 100): array
    {
        return [];
    }
}
