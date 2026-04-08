<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class FixtureDemoTest extends TestCase
{
    public function testDemoFixtureHasUniqueTagIdsSlugsAndLinks(): void
    {
        $fixture = require dirname(__DIR__).'/fixtures/tag-demo-fixture.php';
        self::assertIsArray($fixture);

        $tags = $fixture['tags'] ?? [];
        $links = $fixture['links'] ?? [];
        self::assertIsArray($tags);
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

        $seen = [];
        foreach ($links as $row) {
            self::assertIsArray($row);
            $key = (string) ($row['entity_type'] ?? '').'|'.(string) ($row['entity_id'] ?? '').'|'.(string) ($row['tag_id'] ?? '');
            self::assertArrayHasKey((string) ($row['tag_id'] ?? ''), $ids);
            self::assertArrayNotHasKey($key, $seen);
            $seen[$key] = true;
        }
    }
}
