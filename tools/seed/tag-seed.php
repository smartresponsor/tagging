<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$data = json_decode((string) file_get_contents($root . '/fixtures/tag-demo.json'), true);
if (!is_array($data)) {
    fwrite(STDERR, "invalid fixture json\n");
    exit(1);
}
$tenant = getenv('TENANT') ?: (string) ($data['tenant'] ?? 'demo');
$dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'app';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$makeId = static function (string $slug): string {
    return strtoupper(substr(hash('sha256', $slug), 0, 26));
};
$insertTag = $pdo->prepare('INSERT INTO tag_entity (id, tenant, slug, name, locale, weight) VALUES (:id,:tenant,:slug,:name,:locale,:weight) ON CONFLICT (tenant, slug) DO UPDATE SET name = EXCLUDED.name, locale = EXCLUDED.locale, weight = EXCLUDED.weight');
$findId = $pdo->prepare('SELECT id FROM tag_entity WHERE tenant = :tenant AND slug = :slug');
$insertLink = $pdo->prepare('INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES (:tenant,:entity_type,:entity_id,:tag_id) ON CONFLICT DO NOTHING');
foreach (($data['tags'] ?? []) as $tag) {
    $slug = (string) ($tag['slug'] ?? '');
    $insertTag->execute([
        'id' => $makeId($slug),
        'tenant' => $tenant,
        'slug' => $slug,
        'name' => (string) ($tag['name'] ?? $slug),
        'locale' => (string) ($tag['locale'] ?? 'en'),
        'weight' => (int) ($tag['weight'] ?? 0),
    ]);
}
foreach (($data['links'] ?? []) as $link) {
    $findId->execute(['tenant' => $tenant, 'slug' => (string) ($link['slug'] ?? '')]);
    $tagId = $findId->fetchColumn();
    if (!is_string($tagId) || $tagId === '') {
        continue;
    }
    $insertLink->execute([
        'tenant' => $tenant,
        'entity_type' => (string) ($link['entity_type'] ?? ''),
        'entity_id' => (string) ($link['entity_id'] ?? ''),
        'tag_id' => $tagId,
    ]);
}
echo "tag-seed: ok\n";
