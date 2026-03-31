<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagReadPayloadContractTest extends TestCase
{
    public function testOpenApiDescribesFlatSearchAndSuggestPayloads(): void
    {
        $contract = file_get_contents(__DIR__ . '/../contracts/http/tag-openapi.yaml');

        self::assertIsString($contract);
        self::assertStringContainsString('Flat OK payload `{ ok, items, total, nextPageToken, cacheHit }` without nested `result`', $contract);
        self::assertStringContainsString('Flat OK payload `{ ok, items, cacheHit }` without nested `result`', $contract);
    }
}
