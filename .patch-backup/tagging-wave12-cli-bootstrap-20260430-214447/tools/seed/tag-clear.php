<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$tenant = getenv('TENANT') ?: 'demo';
$dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'app';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach (['tag_link', 'tag_synonym', 'tag_relation', 'tag_entity'] as $table) {
    $stmt = $pdo->prepare('DELETE FROM ' . $table . ' WHERE tenant = :tenant');
    $stmt->execute(['tenant' => $tenant]);
}
echo "tag-clear: ok\n";
