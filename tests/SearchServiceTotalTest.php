<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Cache\Store\Tag\SearchCache;
use App\Service\Core\Tag\SearchService;
use App\Service\Core\Tag\TagReadModelInterface;
use PHPUnit\Framework\TestCase;

final class SearchServiceTotalTest extends TestCase
{
    public function testSearchReturnsAuthoritativeTotalAndStableNextPageToken(): void
    {
        $service = new SearchService($this->readModel(), new SearchCache($this->cacheDir('total')));

        $result = $service->search('demo', 'summer', 1);

        self::assertSame(2, $result['total']);
        self::assertCount(1, $result['items']);
        self::assertSame('summer-sale', $result['items'][0]['slug']);
        self::assertSame(base64_encode('1'), $result['nextPageToken']);
        self::assertFalse($result['cacheHit']);
    }

    public function testSearchCachePreservesAuthoritativeTotal(): void
    {
        $service = new SearchService($this->readModel(), new SearchCache($this->cacheDir('cache')));

        $first = $service->search('demo', 'summer', 2);
        $second = $service->search('demo', 'summer', 2);

        self::assertSame(2, $first['total']);
        self::assertSame(2, $second['total']);
        self::assertFalse($first['cacheHit']);
        self::assertTrue($second['cacheHit']);
        self::assertNull($first['nextPageToken']);
        self::assertNull($second['nextPageToken']);
    }

    private function readModel(): TagReadModelInterface
    {
        return new class() implements TagReadModelInterface {
            public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
            {
                $items = [
                    [
                        'id' => '01HTESTSEARCH0000000000000',
                        'slug' => 'summer-sale',
                        'name' => 'Summer Sale',
                        'locale' => 'en',
                        'weight' => 10,
                    ],
                    [
                        'id' => '01HTESTSEARCH0000000000001',
                        'slug' => 'summer-hats',
                        'name' => 'Summer Hats',
                        'locale' => 'en',
                        'weight' => 8,
                    ],
                ];

                return array_slice($items, $offset, $limit);
            }

            public function countSearch(string $tenant, string $q): int
            {
                return 2;
            }

            public function suggest(string $tenant, string $q, int $limit = 10): array
            {
                return [];
            }

            public function linksForTag(string $tenant, string $tagId, int $limit = 100): array
            {
                return [];
            }

            public function tagsForEntity(string $tenant, string $etype, string $eid, int $limit = 100): array
            {
                return [];
            }
        };
    }

    private function cacheDir(string $suffix): string
    {
        return sys_get_temp_dir() . '/smartresponsor-tagging-search-' . $suffix . '-' . bin2hex(random_bytes(4));
    }
}
