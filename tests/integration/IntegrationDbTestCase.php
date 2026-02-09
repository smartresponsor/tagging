<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;

abstract class IntegrationDbTestCase extends TestCase
{
    protected function getEnv(string $key, string $default): string
    {
        $v = \getenv($key);
        if ($v === false || $v === '') {
            return $default;
        }
        return $v;
    }

    protected function createPdo(): PDO
    {
        if (!\extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped('pdo_pgsql extension is not available on this PHP runtime.');
        }

        $db = $this->getEnv('POSTGRES_DB', 'app');
        $user = $this->getEnv('POSTGRES_USER', 'app');
        $pass = $this->getEnv('POSTGRES_PASSWORD', 'app');
        $host = $this->getEnv('DB_HOST', '127.0.0.1');
        $port = $this->getEnv('DB_PORT', '5432');

        $dsn = \sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $db);
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }
}
