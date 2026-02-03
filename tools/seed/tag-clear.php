<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'app';
$tenant = getenv('TENANT') ?: 'demo';
$pdo = new PDO($dsn, $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('BEGIN');
$pdo->prepare("DELETE FROM tag_link WHERE tenant=:t AND tag_id IN (SELECT id FROM tag_entity WHERE tenant=:t AND slug LIKE 'demo-tag-%')")->execute([':t'=>$tenant]);
$pdo->prepare("DELETE FROM tag_entity WHERE tenant=:t AND slug LIKE 'demo-tag-%'")->execute([':t'=>$tenant]);
$pdo->exec('COMMIT');
echo json_encode(['ok'=>true,'cleared'=>true,'tenant'=>$tenant]) . PHP_EOL;
