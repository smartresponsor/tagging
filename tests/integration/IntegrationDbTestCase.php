<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

abstract class IntegrationDbTestCase extends TestCase
{
    private static ?PDO $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $pdo = self::pdo();
        $this->bootstrapSchema($pdo);
        $this->resetData($pdo);
    }

    protected static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = getenv('DB_DSN') ?: 'pgsql:host=127.0.0.1;port=55432;dbname=tag_test';
        $user = getenv('DB_USER') ?: 'tag';
        $pass = getenv('DB_PASS') ?: 'tag';

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            self::markTestSkipped(sprintf('Integration DB is not available: %s', $e->getMessage()));
        }

        return self::$pdo;
    }

    private function bootstrapSchema(PDO $pdo): void
    {
        $pdo->exec(
            <<<'SQL'
CREATE TABLE IF NOT EXISTS tag_entity (
    id text NOT NULL,
    tenant text NOT NULL,
    slug text NOT NULL,
    name text NOT NULL,
    PRIMARY KEY (tenant, id)
);

CREATE TABLE IF NOT EXISTS tag_link (
    tenant text NOT NULL,
    entity_type text NOT NULL,
    entity_id text NOT NULL,
    tag_id text NOT NULL,
    PRIMARY KEY (tenant, entity_type, entity_id, tag_id),
    FOREIGN KEY (tenant, tag_id) REFERENCES tag_entity (tenant, id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS outbox_event (
    id bigserial PRIMARY KEY,
    tenant text NOT NULL,
    topic text NOT NULL,
    payload jsonb NOT NULL,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS idempotency_store (
    tenant text NOT NULL,
    key text NOT NULL,
    op text NOT NULL,
    checksum text NOT NULL,
    status text NOT NULL,
    result_json jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, key)
);
SQL
        );
    }

    private function resetData(PDO $pdo): void
    {
        $pdo->exec('TRUNCATE TABLE tag_link, outbox_event, idempotency_store, tag_entity RESTART IDENTITY');
    }
}
