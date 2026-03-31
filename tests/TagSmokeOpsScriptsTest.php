<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagSmokeOpsScriptsTest extends TestCase
{
    public function testSmokeRuntimeScriptValidatesCurrentPublicSurface(): void
    {
        $script = file_get_contents(__DIR__ . '/../tools/smoke/tag-smoke.php');
        self::assertIsString($script);
        self::assertStringContainsString('/tag/assignments/bulk', $script);
        self::assertStringContainsString('/tag/assignments/bulk-to-entity', $script);
        self::assertStringContainsString('tag_not_found', $script);
        self::assertStringContainsString('search_nested_result_present', $script);
        self::assertStringContainsString('seed_search_shape_failed', $script);
    }

    public function testSyntheticScriptsProbeStatusSurfaceSearchAndSuggest(): void
    {
        $gate = file_get_contents(__DIR__ . '/../tools/synthetic/tag-slo-gate.sh');
        $smoke = file_get_contents(__DIR__ . '/../tools/synthetic/slo.sh');

        self::assertIsString($gate);
        self::assertIsString($smoke);
        self::assertStringContainsString('/tag/_surface', $gate);
        self::assertStringContainsString('/tag/suggest', $gate);
        self::assertStringContainsString('/tag/_surface', $smoke);
        self::assertStringContainsString('/tag/suggest', $smoke);
    }

    public function testReadmeDocumentsSmokeRuntimeCoverage(): void
    {
        $readme = file_get_contents(__DIR__ . '/../README.md');
        self::assertIsString($readme);
        self::assertStringContainsString('composer run -n smoke:runtime', $readme);
        self::assertStringContainsString('bulk assignment endpoints, missing-tag unassign semantics, flat read payloads, and authoritative search totals', $readme);
    }
}
