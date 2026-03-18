<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';
$dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'app';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$files = glob(tag_root('db/postgres/migrations/*.sql')) ?: [];
sort($files, SORT_STRING);
foreach ($files as $file) {
    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        continue;
    }
    $pdo->exec($sql);
    fwrite(STDOUT, basename($file) . PHP_EOL);
}
