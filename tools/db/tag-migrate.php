<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
$root = require __DIR__ . '/../_bootstrap.php';

/**
 * Prefer explicit DB env, but fall back to the docker-backed test DB state file
 * so `composer db:test:start` followed by `composer db:smoke` works without eval.
 */
$testDbPortStateFile = (getenv('TMPDIR') ?: '/tmp') . '/smartresponsor-tag-test-db-port';
$testDbPort = is_file($testDbPortStateFile) ? trim((string) file_get_contents($testDbPortStateFile)) : '';

$dsn = getenv('DB_DSN') ?: ($testDbPort !== '' ? sprintf('pgsql:host=127.0.0.1;port=%s;dbname=tag_test', $testDbPort) : 'pgsql:host=localhost;port=5432;dbname=app');
$user = getenv('DB_USER') ?: ($testDbPort !== '' ? 'tag' : 'app');
$pass = getenv('DB_PASS') ?: ($testDbPort !== '' ? 'tag' : 'app');
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$files = glob($root . '/db/postgres/migrations/*.sql') ?: [];

$migrationOrder = [
    '2025_10_27_tag.sql' => 10,
    '2025_10_27_idempotency_outbox.sql' => 20,
    '2025_10_27_slug_policy.sql' => 30,
    '2026_02_02_tag_full.sql' => 40,
    '2026_02_02_tag_entity_type_unbound.sql' => 50,
];

usort($files, static function (string $left, string $right) use ($migrationOrder): int {
    $leftName = basename($left);
    $rightName = basename($right);
    $leftPriority = $migrationOrder[$leftName] ?? 1000;
    $rightPriority = $migrationOrder[$rightName] ?? 1000;

    return [$leftPriority, $leftName] <=> [$rightPriority, $rightName];
});

foreach ($files as $file) {
    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        continue;
    }
    $pdo->exec($sql);
    fwrite(STDOUT, basename($file) . PHP_EOL);
}
