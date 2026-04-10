<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Cache\Store\Tag\SearchCache;
use App\Cache\Store\Tag\SuggestCache;
use App\Http\Api\Tag\AssignmentReadController;
use App\Http\Api\Tag\SearchController;
use App\Http\Api\Tag\SuggestController;
use App\Service\Core\Tag\SearchService;
use App\Service\Core\Tag\SuggestService;
use App\Service\Core\Tag\TagReadModelInterface;
use PHPUnit\Framework\TestCase;

final class SearchSuggestControllerContractTest extends TestCase
{
    public function testSearchControllerReturnsCanonicalTopLevelResponse(): void
    {
        $read = new class implements TagReadModelInterface {
            public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
            {
                return [['id' => 't1', 'slug' => 'priority', 'name' => 'Priority', 'locale' => null, 'weight' => 10]];
            }

            public function countSearch(string $tenant, string $q): int
            {
                return 1;
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

        $baseDir = sys_get_temp_dir() . '/tagging-search-contract-' . bin2hex(random_bytes(4));
        $controller = new SearchController(new SearchService($read, new SearchCache($baseDir . '/search', 60)));

        [$status, $headers, $body] = $controller->get([
            'headers' => ['X-Tenant-Id' => 'demo'],
            'query' => ['q' => 'priority', 'pageSize' => 10],
        ]);

        $payload = json_decode($body, true);

        self::assertSame(200, $status);
        self::assertSame('application/json', $headers['Content-Type'] ?? null);
        self::assertTrue($payload['ok']);
        self::assertSame('priority', $payload['items'][0]['slug'] ?? null);
        self::assertArrayNotHasKey('result', $payload);
    }

    public function testSuggestControllerBlankQueryReturnsEmptyItemsAndNotValidationFailure(): void
    {
        $read = new class implements TagReadModelInterface {
            public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
            {
                return [];
            }

            public function countSearch(string $tenant, string $q): int
            {
                throw new \RuntimeException('countSearch must not be called for blank query');
            }

            public function suggest(string $tenant, string $q, int $limit = 10): array
            {
                throw new \RuntimeException('suggest must not be called for blank query');
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

        $baseDir = sys_get_temp_dir() . '/tagging-suggest-contract-' . bin2hex(random_bytes(4));
        $controller = new SuggestController(new SuggestService($read, new SuggestCache($baseDir . '/suggest', 60)));

        [$status, , $body] = $controller->get([
            'headers' => ['x-tenant-id' => 'demo'],
            'query' => ['q' => '   '],
        ]);

        $payload = json_decode($body, true);

        self::assertSame(200, $status);
        self::assertTrue($payload['ok']);
        self::assertSame([], $payload['items']);
        self::assertArrayNotHasKey('result', $payload);
        self::assertFalse($payload['cacheHit']);
    }

    public function testAssignmentReadControllerAcceptsCanonicalTenantHeaderCasing(): void
    {
        $read = new class implements TagReadModelInterface {
            public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
            {
                return [];
            }

            public function countSearch(string $tenant, string $q): int
            {
                return 0;
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
                return [['id' => 't1', 'slug' => 'priority', 'name' => 'Priority']];
            }
        };

        $controller = new AssignmentReadController($read);
        [$status, , $body] = $controller->listByEntity([
            'headers' => ['X-Tenant-Id' => 'demo'],
            'query' => ['entityType' => 'product', 'entityId' => 'p1'],
        ]);

        $payload = json_decode($body, true);

        self::assertSame(200, $status);
        self::assertTrue($payload['ok']);
        self::assertSame('priority', $payload['items'][0]['slug'] ?? null);
    }
}
