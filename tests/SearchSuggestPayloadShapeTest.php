<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Cache\Store\Tag\SearchCache;
use App\Cache\Store\Tag\SuggestCache;
use App\Http\Api\Tag\SearchController;
use App\Http\Api\Tag\SuggestController;
use App\Service\Core\Tag\SearchService;
use App\Service\Core\Tag\SuggestService;
use App\Service\Core\Tag\TagReadModelInterface;
use PHPUnit\Framework\TestCase;

final class SearchSuggestPayloadShapeTest extends TestCase
{
    public function testSearchControllerReturnsFlatPayloadWithoutNestedResult(): void
    {
        $controller = new SearchController(
            new SearchService(
                $this->readModel(),
                new SearchCache($this->cacheDir('search')),
            ),
        );

        [$status, $headers, $body] = $controller->get([
            'headers' => ['X-Tenant-Id' => 'demo'],
            'query' => ['q' => 'summer', 'pageSize' => 5],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertSame('application/json', $headers['Content-Type']);
        self::assertTrue($payload['ok']);
        self::assertSame(1, $payload['total']);
        self::assertFalse($payload['cacheHit']);
        self::assertSame('summer-sale', $payload['items'][0]['slug']);
        self::assertArrayNotHasKey('result', $payload);
    }

    public function testSuggestControllerReturnsFlatPayloadWithoutNestedResult(): void
    {
        $controller = new SuggestController(
            new SuggestService(
                $this->readModel(),
                new SuggestCache($this->cacheDir('suggest')),
            ),
        );

        [$status, $headers, $body] = $controller->get([
            'headers' => ['X-Tenant-Id' => 'demo'],
            'query' => ['q' => 'sum', 'limit' => 5],
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $status);
        self::assertSame('application/json', $headers['Content-Type']);
        self::assertTrue($payload['ok']);
        self::assertFalse($payload['cacheHit']);
        self::assertSame('summer-sale', $payload['items'][0]['slug']);
        self::assertArrayNotHasKey('result', $payload);
    }

    private function readModel(): TagReadModelInterface
    {
        return new class() implements TagReadModelInterface {
            public function search(string $tenant, string $q, int $limit = 20, int $offset = 0): array
            {
                return [[
                    'id' => '01HTESTSEARCH0000000000000',
                    'slug' => 'summer-sale',
                    'name' => 'Summer Sale',
                    'locale' => 'en',
                    'weight' => 10,
                ]];
            }

            public function countSearch(string $tenant, string $q): int
            {
                return 1;
            }

            public function suggest(string $tenant, string $q, int $limit = 10): array
            {
                return [[
                    'slug' => 'summer-sale',
                    'name' => 'Summer Sale',
                ]];
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
        return sys_get_temp_dir() . '/smartresponsor-tagging-' . $suffix . '-' . bin2hex(random_bytes(4));
    }
}
