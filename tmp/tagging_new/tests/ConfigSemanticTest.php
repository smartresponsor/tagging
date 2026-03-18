<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class ConfigSemanticTest extends TestCase
{
    public function testQuotaAndAssignmentConfigAreNormalized(): void
    {
        $quota = (string) file_get_contents(dirname(__DIR__) . '/config/tag_quota.yaml');
        $assignment = (string) file_get_contents(dirname(__DIR__) . '/config/tag_assignment.yaml');

        self::assertStringNotContainsString('\\n', $quota);
        self::assertStringContainsString('enforce: true', $quota);
        self::assertStringContainsString('driver: pdo', $assignment);
        self::assertStringContainsString('table: tag_link', $assignment);
    }
}
