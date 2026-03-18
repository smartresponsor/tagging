<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

abstract class IntegrationDbTestCase extends TestCase
{
    protected function getEnv(string $key, string $default): string
    {
        $v = \getenv($key);
        if (false === $v || '' === $v) {
            return $default;
        }

        return $v;
    }

    protected function createPdo(): \PDO
    {
        if (!\extension_loaded('pdo_pgsql')) {
            static::markTestSkipped('pdo_pgsql extension is not available on this PHP runtime.');
        }

        $db = $this->getEnv('POSTGRES_DB', 'app');
        $user = $this->getEnv('POSTGRES_USER', 'app');
        $pass = $this->getEnv('POSTGRES_PASSWORD', 'app');
        $host = $this->getEnv('DB_HOST', '127.0.0.1');
        $port = $this->getEnv('DB_PORT', '5432');

        $dsn = \sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $db);

        try {
            return new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            static::markTestSkipped(\sprintf('PostgreSQL integration database is not reachable: %s', $e->getMessage()));
        }
    }

    protected function pdo(): \PDO
    {
        return $this->createPdo();
    }
}
