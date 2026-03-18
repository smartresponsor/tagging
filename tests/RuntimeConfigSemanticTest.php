<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RuntimeConfigSemanticTest extends TestCase
{
    public function testQuotaConfigUsesBooleanToken(): void
    {
        $text = (string) file_get_contents(dirname(__DIR__).'/config/tag_quota.yaml');
        self::assertMatchesRegularExpression('/^enforce:\s*(true|false)\s*$/m', $text);
        self::assertStringNotContainsString('\\n', $text);
    }

    public function testAssignmentConfigDeclaresPdoBackedStore(): void
    {
        $text = (string) file_get_contents(dirname(__DIR__).'/config/tag_assignment.yaml');
        self::assertStringContainsString('driver: pdo', $text);
        self::assertStringNotContainsString('assignment.ndjson', $text);
    }
}
