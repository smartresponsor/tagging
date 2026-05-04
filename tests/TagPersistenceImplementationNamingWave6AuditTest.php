<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagPersistenceImplementationNamingWave6AuditTest extends TestCase
{
    public function testPersistenceImplementationNamingAuditPasses(): void
    {
        $root = dirname(__DIR__);
        $audit = $root . '/tools/audit/tag-persistence-implementation-naming-audit.php';

        self::assertFileExists($audit);

        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($audit) . ' 2>&1';
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
    }
}
