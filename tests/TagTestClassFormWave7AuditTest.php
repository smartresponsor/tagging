<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagTestClassFormWave7AuditTest extends TestCase
{
    public function testTagTestClassFormAuditPasses(): void
    {
        $root = dirname(__DIR__);
        $command = sprintf('php %s 2>&1', escapeshellarg($root . '/tools/audit/tag-test-class-form-audit.php'));

        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
    }
}
