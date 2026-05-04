<?php

declare(strict_types=1);

namespace Tests;

trait RequiresSqlite
{
    private function requireSqlite(): void
    {
        if (!\extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite extension is not available on this PHP runtime.');
        }
    }
}
