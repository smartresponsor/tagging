<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagRuntimeRunbookTruthTest extends TestCase
{
    public function testRunbookReflectsCurrentPublicShellAndOperationalPosture(): void
    {
        $doc = file_get_contents(__DIR__ . '/../docs/ops/runbook.md');
        self::assertIsString($doc);

        self::assertStringContainsString('GET /tag/_status', $doc);
        self::assertStringContainsString('GET /tag/_surface', $doc);
        self::assertStringContainsString('/tag/assignments/bulk', $doc);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $doc);
        self::assertStringContainsString('404 tag_not_found', $doc);
        self::assertStringContainsString('no `/tag/_metrics` endpoint', $doc);
        self::assertStringContainsString('docs/api/error-catalog.md', $doc);
        self::assertStringContainsString('current shipped runtime', $doc);
    }
}
