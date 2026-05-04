<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class CanonicalStructureAuditTest extends TestCase
{
    public function testCanonicalStructureAuditPasses(): void
    {
        $script = __DIR__ . '/../tools/audit/tag-canonical-structure-audit.php';
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' 2>&1';
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
    }
}
