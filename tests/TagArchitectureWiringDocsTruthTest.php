<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagArchitectureWiringDocsTruthTest extends TestCase
{
    public function testHttpWiringDocReflectsCurrentBulkSurfaceAndRouteTruth(): void
    {
        $doc = file_get_contents(__DIR__ . '/../docs/http/http-wiring.md');

        self::assertIsString($doc);
        self::assertStringContainsString('/tag/assignments/bulk', $doc);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $doc);
        self::assertStringContainsString('tag.yaml', $doc);
        self::assertStringContainsString('authoritative', $doc);
        self::assertStringContainsString('`total`', $doc);
        self::assertStringContainsString('tag_not_found', $doc);
        self::assertStringNotContainsString('bulk assignment routes', $doc);
    }

    public function testHostMinimalDeployExampleUsesSharedReadModelForSuggest(): void
    {
        $doc = file_get_contents(__DIR__ . '/../docs/deploy/host-minimal-search.md');

        self::assertIsString($doc);
        self::assertStringContainsString('new App\Infrastructure\ReadModel\Tag\TagReadModel($pdo)', $doc);
        self::assertStringContainsString('new App\Service\Core\Tag\SuggestService($read, $suggestCache)', $doc);
        self::assertStringContainsString('bulk assignments', $doc);
        self::assertStringNotContainsString('new App\Service\Core\Tag\SuggestService($pdo, $suggestCache)', $doc);
    }
}
