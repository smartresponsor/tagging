<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Tag;

final class PdoConnectionFactory
{
    public static function createFromEnvironment(): \PDO
    {
        $dsn = self::dbDsn();
        $user = self::dbUser();
        $pass = self::dbPass();

        return new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    private static function env(string $name, string $default): string
    {
        $value = getenv($name);

        return is_string($value) && '' !== $value ? $value : $default;
    }

    private static function envNullable(string $name): ?string
    {
        $value = getenv($name);

        return is_string($value) && '' !== $value ? $value : null;
    }

    private static function dbDsn(): string
    {
        $dsn = self::envNullable('DB_DSN');
        if (null !== $dsn) {
            return $dsn;
        }

        $host = self::env('DB_HOST', 'localhost');
        $port = self::env('DB_PORT', '5432');
        $name = self::env('POSTGRES_DB', 'app');

        return sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);
    }

    private static function dbUser(): string
    {
        $user = self::envNullable('DB_USER');
        if (null !== $user) {
            return $user;
        }

        return self::env('POSTGRES_USER', 'app');
    }

    private static function dbPass(): string
    {
        $pass = self::envNullable('DB_PASS');
        if (null !== $pass) {
            return $pass;
        }

        return self::env('POSTGRES_PASSWORD', 'app');
    }
}
