<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagDemoSurfaceExamplesTest extends TestCase
{
    public function testDemoCatalogCarriesCurrentSurfaceTourMetadata(): void
    {
        $catalog = require __DIR__ . '/../fixtures/tag-demo-catalog.php';

        self::assertIsArray($catalog);
        self::assertSame('01HMISSINGTAG0000000000000', $catalog['missing_tag_id'] ?? null);
        self::assertSame('tag_not_found', $catalog['write_contract']['missing_tag_unassign_code'] ?? null);
        self::assertTrue($catalog['write_contract']['search_payload_is_flat'] ?? false);
        self::assertTrue($catalog['write_contract']['search_total_is_authoritative'] ?? false);
        self::assertSame('/tag/assignments/bulk', $catalog['tour']['bulk'] ?? null);
        self::assertSame('/tag/assignments/bulk-to-entity', $catalog['tour']['bulk_to_entity'] ?? null);
        self::assertSame('/tag/01HMISSINGTAG0000000000000/unassign', $catalog['tour']['missing_unassign'] ?? null);
    }

    public function testDemoHttpExamplesIncludeBulkAndMissingTagFlows(): void
    {
        $tour = file_get_contents(__DIR__ . '/../public/tag/examples/tour.http');
        $requests = file_get_contents(__DIR__ . '/../public/tag/demo/requests.http');

        self::assertIsString($tour);
        self::assertIsString($requests);
        self::assertStringContainsString('/tag/assignments/bulk', $tour);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $tour);
        self::assertStringContainsString('tag_not_found', $tour);
        self::assertStringContainsString('/tag/assignments/bulk', $requests);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $requests);
        self::assertStringContainsString('tag_not_found', $requests);
    }
}
