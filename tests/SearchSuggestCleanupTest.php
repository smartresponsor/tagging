<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Cache\Store\Tag\SearchCache;
use App\Cache\Store\Tag\SuggestCache;
use App\Service\Core\Tag\SearchService;
use App\Service\Core\Tag\SuggestService;
use App\Service\Core\Tag\TagReadModelInterface;
use PHPUnit\Framework\TestCase;

final class SearchSuggestCleanupTest extends TestCase
{
    public function testSearchAndSuggestUseUnifiedReadModelContract(): void
    {
        $read = new class implements TagReadModelInterface {
            public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
            {
                return [['id' => 't1', 'slug' => 'priority', 'name' => 'Priority', 'locale' => null, 'weight' => 100]];
            }

            public function countSearch(string $tenant, string $q): int
            {
                return 1;
            }

            public function suggest(string $tenant, string $q, int $limit = 10): array
            {
                return [['slug' => 'priority', 'name' => 'Priority']];
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

        $baseDir = sys_get_temp_dir().'/tagging-search-suggest-'.bin2hex(random_bytes(4));
        $search = new SearchService($read, new SearchCache($baseDir.'/search', 60));
        $suggest = new SuggestService($read, new SuggestCache($baseDir.'/suggest', 60));

        $searchPayload = $search->search('demo', ' priority ', 10);
        $suggestPayload = $suggest->suggest('demo', ' pri ', 5);

        self::assertSame('priority', $searchPayload['items'][0]['slug']);
        self::assertSame('priority', $suggestPayload['items'][0]['slug']);
    }

    public function testSearchAndSuggestShortCircuitBlankQuery(): void
    {
        $read = new class implements TagReadModelInterface {
            public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
            {
                throw new \RuntimeException('search should not be called for blank query');
            }

            public function countSearch(string $tenant, string $q): int
            {
                throw new \RuntimeException('countSearch should not be called for blank query');
            }

            public function suggest(string $tenant, string $q, int $limit = 10): array
            {
                throw new \RuntimeException('suggest should not be called for blank query');
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

        $baseDir = sys_get_temp_dir().'/tagging-search-suggest-empty-'.bin2hex(random_bytes(4));
        $search = new SearchService($read, new SearchCache($baseDir.'/search', 60));
        $suggest = new SuggestService($read, new SuggestCache($baseDir.'/suggest', 60));

        self::assertSame(['items' => [], 'total' => 0, 'nextPageToken' => null, 'cacheHit' => false], $search->search('demo', '   ', 10));
        self::assertSame(['items' => [], 'cacheHit' => false], $suggest->suggest('demo', '   ', 5));
    }

    public function testLegacySearchCacheTreeIsGone(): void
    {
        self::assertDirectoryDoesNotExist(dirname(__DIR__).'/src/Cache/Search');
    }
}
