<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class CoreBoundaryAuditTest extends TestCase
{
    public function testCoreBoundaryAuditPassesOnCurrentRepository(): void
    {
        $root = dirname(__DIR__);
        $cmd = sprintf('php %s', escapeshellarg($root.'/tools/audit/tag-core-boundary-audit.php'));
        exec($cmd.' 2>&1', $output, $code);

        self::assertSame(0, $code, implode('
', $output));
    }
}
