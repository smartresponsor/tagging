<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagAdminShellSurfaceTest extends TestCase
{
    public function testAdminShellExposesBulkSurfaceInMarkup(): void
    {
        $html = file_get_contents(__DIR__.'/../admin/index.html');

        self::assertIsString($html);
        self::assertStringContainsString('data-tab="bulk"', $html);
        self::assertStringContainsString('id="btnBulkAssignments"', $html);
        self::assertStringContainsString('id="btnBulkToEntity"', $html);
        self::assertStringContainsString('id="btnMissingUnassign"', $html);
        self::assertStringContainsString('bulk operations', $html);
    }

    public function testAdminShellScriptUsesFlatPayloadAssumptionAndBulkRoutes(): void
    {
        $script = file_get_contents(__DIR__.'/../admin/app.js');

        self::assertIsString($script);
        self::assertStringContainsString("'/tag/assignments/bulk'", $script);
        self::assertStringContainsString("'/tag/assignments/bulk-to-entity'", $script);
        self::assertStringContainsString('bulkMissingTagId', $script);
        self::assertStringContainsString("const id = parsed && parsed.id ? String(parsed.id) : ''", $script);
        self::assertStringNotContainsString('parsed && parsed.result && parsed.result.id', $script);
    }
}
