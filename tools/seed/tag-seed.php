<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$loadFixture = require __DIR__ . '/tag-demo-fixture-loader.php';
$data = $loadFixture($root);
$tenant = (string) (getenv('TENANT') ?: ($data['tenant'] ?? 'demo'));
$dsn = (string) (getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app');
$user = (string) (getenv('DB_USER') ?: 'app');
$pass = (string) (getenv('DB_PASS') ?: 'app');
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$insertTag = $pdo->prepare(
    'INSERT INTO tag_entity (id, tenant, slug, name, locale, weight) '
    . 'VALUES (:id,:tenant,:slug,:name,:locale,:weight) '
    . 'ON CONFLICT (tenant, slug) DO UPDATE SET '
    . 'id = EXCLUDED.id, '
    . 'name = EXCLUDED.name, '
    . 'locale = EXCLUDED.locale, '
    . 'weight = EXCLUDED.weight'
);
$insertLink = $pdo->prepare(
    'INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) '
    . 'VALUES (:tenant,:entity_type,:entity_id,:tag_id) '
    . 'ON CONFLICT DO NOTHING'
);

foreach (($data['tags'] ?? []) as $tag) {
    $insertTag->execute([
        ':id' => (string) ($tag['id'] ?? ''),
        ':tenant' => $tenant,
        ':slug' => (string) ($tag['slug'] ?? ''),
        ':name' => (string) ($tag['name'] ?? ''),
        ':locale' => (string) ($tag['locale'] ?? 'en'),
        ':weight' => (int) ($tag['weight'] ?? 0),
    ]);
}

foreach (($data['links'] ?? []) as $link) {
    $insertLink->execute([
        ':tenant' => $tenant,
        ':entity_type' => (string) ($link['entity_type'] ?? ''),
        ':entity_id' => (string) ($link['entity_id'] ?? ''),
        ':tag_id' => (string) ($link['tag_id'] ?? ''),
    ]);
}

echo "tag-seed: ok\n";
