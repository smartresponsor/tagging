<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class FixtureCatalogTest extends TestCase
{
    public function testCatalogReferencesKnownFixtureIds(): void
    {
        $fixture = require dirname(__DIR__).'/fixtures/tag-demo-fixture.php';
        $catalog = require dirname(__DIR__).'/fixtures/tag-demo-catalog.php';
        $ids = array_column($fixture['tags'] ?? [], 'id');

        self::assertContains($catalog['primary_tag_id'], $ids);
        self::assertSame('demo', $catalog['tenant']);
        self::assertSame('product', $catalog['assignment_entity_type']);
        self::assertSame('demo-product-1', $catalog['assignment_entity_id']);
    }
}
