<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagDocsContractTest extends TestCase
{
    public function testDocsAndExamplesDescribeBulkSurfaceAndMissingTagUnassign(): void
    {
        $readme = file_get_contents(__DIR__ . '/../README.md');
        $quickDemo = file_get_contents(__DIR__ . '/../docs/demo/tag-quick-demo.md');
        $httpExample = file_get_contents(__DIR__ . '/../public/tag/examples/http.http');

        self::assertIsString($readme);
        self::assertIsString($quickDemo);
        self::assertIsString($httpExample);

        self::assertStringContainsString('bulk assignment operations', $readme);
        self::assertStringContainsString('404 `tag_not_found` unassign contract', $readme);
        self::assertStringContainsString('/tag/assignments/bulk', $quickDemo);
        self::assertStringContainsString('Expect an HTTP `404` payload with `code=tag_not_found`', $quickDemo);
        self::assertStringContainsString('/tag/{{missingTagId}}/unassign', $httpExample);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $httpExample);
    }
}
