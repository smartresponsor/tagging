<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class ProtocolAuditTest extends TestCase
{
    public function testProtocolAuditPassesOnCurrentRepository(): void
    {
        $root = dirname(__DIR__);
        $script = $root . '/tools/audit/tag-protocol-audit.php';

        self::assertFileExists($script);

        $output = [];
        $code = 0;
        exec('php ' . escapeshellarg($script) . ' 2>&1', $output, $code);

        self::assertSame(0, $code, implode('
', $output));
        self::assertStringContainsString('protocol audit passed', strtolower(implode('
', $output)));
    }

    public function testRootDoesNotContainLegacyDuplicateTagConfigOrTransientWorkspaces(): void
    {
        $root = dirname(__DIR__);
        $forbidden = [
            'tag_assignment.yaml',
            'tag_quota.yaml',
            'tag_cons_patched',
            'tag_fix',
            'tmp',
        ];

        foreach ($forbidden as $path) {
            self::assertFalse(file_exists($root . DIRECTORY_SEPARATOR . $path), 'Forbidden protocol residue exists: ' . $path);
        }
    }
}
