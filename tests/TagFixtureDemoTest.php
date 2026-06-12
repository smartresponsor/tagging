<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagFixtureDemoTest extends TestCase
{
    public function testDemoFixtureHasUniqueTagIdsSlugsAndLinks(): void
    {
        $fixture = require dirname(__DIR__) . '/fixtures/tag-demo-fixture.php';
        self::assertIsArray($fixture);

        $tags = $fixture['tags'] ?? [];
        $adminRows = $fixture['admin_rows'] ?? [];
        $links = $fixture['links'] ?? [];
        self::assertIsArray($tags);
        self::assertIsArray($adminRows);
        self::assertIsArray($links);

        $ids = [];
        $slugs = [];
        foreach ($tags as $row) {
            self::assertIsArray($row);
            $id = (string) ($row['id'] ?? '');
            $slug = (string) ($row['slug'] ?? '');
            self::assertNotSame('', $id);
            self::assertNotSame('', $slug);
            self::assertArrayNotHasKey($id, $ids);
            self::assertArrayNotHasKey($slug, $slugs);
            $ids[$id] = true;
            $slugs[$slug] = true;
        }

        $adminIds = [];
        foreach ($adminRows as $row) {
            self::assertIsArray($row);
            self::assertIsInt($row['id'] ?? null);
            self::assertGreaterThan(0, $row['id']);
            $tagId = (string) ($row['tag_id'] ?? '');
            self::assertNotSame('', $tagId);
            self::assertArrayHasKey($tagId, $ids);
            self::assertArrayNotHasKey((string) $row['id'], $adminIds);
            $adminIds[(string) $row['id']] = true;
        }

        $seen = [];
        foreach ($links as $row) {
            self::assertIsArray($row);
            $key = (string) ($row['entity_type'] ?? '') . '|' . (string) ($row['entity_id'] ?? '') . '|' . (string) ($row['tag_id'] ?? '');
            self::assertArrayHasKey((string) ($row['tag_id'] ?? ''), $ids);
            self::assertArrayNotHasKey($key, $seen);
            $seen[$key] = true;
        }
    }
}
