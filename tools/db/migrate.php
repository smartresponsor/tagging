<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/host-minimal/autoload.php';

$dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'app';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$dir = dirname(__DIR__, 2) . '/db/postgres/migrations';
$files = glob($dir . '/*.sql') ?: [];

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
    if ($sql === false) {
        fwrite(STDERR, "Cannot read migration: $file\n");
        exit(1);
    }
    $pdo->exec($sql);
    fwrite(STDOUT, 'applied ' . basename($file) . PHP_EOL);
}
