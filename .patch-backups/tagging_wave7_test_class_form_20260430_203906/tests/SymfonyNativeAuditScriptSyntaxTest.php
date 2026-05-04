<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SymfonyNativeAuditScriptSyntaxTest extends TestCase
{
    public function testBootstrapAuditScriptUsesPhpVariables(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/tools/audit/tag-bootstrap-audit.php');
        self::assertIsString($content);

        self::assertStringContainsString('$errors = [];', $content);
        self::assertStringNotContainsString("\nerrors = [];", $content);
    }
}
