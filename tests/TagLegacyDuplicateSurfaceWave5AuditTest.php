<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TagLegacyDuplicateSurfaceWave5AuditTest extends TestCase
{
    public function testRetiredLegacyDuplicateSurfaceAuditDoesNotReturn(): void
    {
        $root = dirname(__DIR__);

        self::assertFileDoesNotExist($root . '/tools/audit/tag-legacy-duplicate-surface-audit.php');
        self::assertDirectoryDoesNotExist($root . '/src/Tagging');
    }
}
