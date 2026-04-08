<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagPublicShellReadSurfaceTruthTest extends TestCase
{
    public function testPublicSurfaceKeepsReadRoutesVisibleAsShippedShell(): void
    {
        $surface = require __DIR__.'/../config/tag_public_surface.php';
        self::assertIsArray($surface);

        $route = $surface['route'] ?? null;
        self::assertIsArray($route);

        self::assertSame('GET /tag/search', $route['search'] ?? null);
        self::assertSame('GET /tag/suggest', $route['suggest'] ?? null);
        self::assertSame('/tag/_status', $route['status'] ?? null);
        self::assertSame('/tag/_surface', $route['discovery'] ?? null);
    }

    public function testRunbookAndReadmeContinueToAdvertiseSearchAndSuggestAsPublicShell(): void
    {
        $runbook = file_get_contents(__DIR__.'/../docs/ops/runbook.md');
        $readme = file_get_contents(__DIR__.'/../README.md');

        self::assertIsString($runbook);
        self::assertIsString($readme);

        self::assertStringContainsString('/tag/search', $runbook);
        self::assertStringContainsString('/tag/suggest', $runbook);
        self::assertStringContainsString('/tag/search', $readme);
        self::assertStringContainsString('/tag/suggest', $readme);
        self::assertStringContainsString('flat', $readme);
        self::assertStringContainsString('authoritative', $readme);
        self::assertStringContainsString('`total`', $readme);
    }

    public function testOpenApiStillDescribesFlatReadPayloads(): void
    {
        $openApi = file_get_contents(__DIR__.'/../contracts/http/tag-openapi.yaml');
        self::assertIsString($openApi);

        self::assertStringContainsString('/tag/search:', $openApi);
        self::assertStringContainsString('/tag/suggest:', $openApi);
        self::assertStringContainsString('Flat OK payload `{ ok, items, total, nextPageToken, cacheHit }`', $openApi);
        self::assertStringContainsString('Flat OK payload `{ ok, items, cacheHit }`', $openApi);
    }
}
