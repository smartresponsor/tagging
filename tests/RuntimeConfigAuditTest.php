<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RuntimeConfigAuditTest extends TestCase
{
    public function testQuotaConfigUsesBoolAndAssignmentConfigUsesPdo(): void
    {
        $quota = (string) file_get_contents(dirname(__DIR__).'/config/tag_quota.yaml');
        $assignment = (string) file_get_contents(dirname(__DIR__).'/config/tag_assignment.yaml');

        self::assertMatchesRegularExpression('/^enforce:\s*(true|false)\s*$/m', $quota);
        self::assertStringNotContainsString('\\n', $quota);
        self::assertMatchesRegularExpression('/driver:\s*pdo/m', $assignment);
        self::assertMatchesRegularExpression('/table:\s*tag_link/m', $assignment);
        self::assertStringNotContainsString('path:', $assignment);
    }
}
