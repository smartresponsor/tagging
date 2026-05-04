<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class ConfigAuditTest extends TestCase
{
    public function testRuntimeConfigSemantics(): void
    {
        $quota = trim((string) file_get_contents(__DIR__ . '/../config/tag_quota.yaml'));
        $assignment = (string) file_get_contents(__DIR__ . '/../config/tag_assignment.yaml');

        self::assertSame('enforce: true', $quota);
        self::assertStringContainsString('driver: pdo', $assignment);
        self::assertStringNotContainsString('driver: file', $assignment);
        self::assertStringNotContainsString('path:', $assignment);
    }
}
