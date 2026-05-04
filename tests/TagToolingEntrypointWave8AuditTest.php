<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class TagToolingEntrypointWave8AuditTest extends TestCase
{
    public function testToolingEntrypointAuditPasses(): void
    {
        $root = dirname(__DIR__);
        $command = sprintf('php %s 2>&1', escapeshellarg($root . '/tools/audit/tag-tooling-entrypoint-audit.php'));

        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
    }
}
