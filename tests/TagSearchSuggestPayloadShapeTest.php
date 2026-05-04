<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use App\Tagging\Cache\Store\Tag\TagSearchCache;
use App\Tagging\Cache\Store\Tag\TagSuggestCache;
use App\Tagging\Http\Api\Tag\TagSearchController;
use App\Tagging\Http\Api\Tag\TagSuggestController;
use App\Tagging\Service\Core\TagSearchService;
use App\Tagging\Service\Core\TagSuggestService;
use App\Tagging\Service\Core\TagReadModelInterface;
use PHPUnit\Framework\TestCase;

final class TagSearchSuggestPayloadShapeTest extends TestCase
{
    public function testSearchControllerReturnsFlatPayloadWithoutNestedResult(): void
    {
        $controller = new TagSearchController(
            new TagSearchService(
                $this->readModel(),
                new TagSearchCache($this->cacheDir('search')),
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
        $controller = new TagSuggestController(
            new TagSuggestService(
                $this->readModel(),
                new TagSuggestCache($this->cacheDir('suggest')),
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
        return new class implements TagReadModelInterface {
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
