<?php

declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';

require __DIR__ . '/tag-fixture-validate.php';

$tenant = (string) (getenv('TENANT') ?: 'demo');
$dsn = (string) (getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app');
$user = (string) (getenv('DB_USER') ?: 'app');
$pass = (string) (getenv('DB_PASS') ?: 'app');

$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$fixture = json_decode((string) file_get_contents($root . '/fixtures/tag-demo.json'), true, 512, JSON_THROW_ON_ERROR);

$makeId = static function (string $slug): string {
    return strtoupper(substr(hash('sha256', $slug), 0, 26));
};

$pdo->beginTransaction();

try {
    $tagCount = 0;
    $linkCount = 0;

    $insertTag = $pdo->prepare('INSERT INTO tag_entity (id, tenant, slug, name, locale, weight) VALUES (:id,:tenant,:slug,:name,:locale,:weight) ON CONFLICT (tenant, slug) DO UPDATE SET name = EXCLUDED.name, locale = EXCLUDED.locale, weight = EXCLUDED.weight');
    $findId = $pdo->prepare('SELECT id FROM tag_entity WHERE tenant = :tenant AND slug = :slug');
    $insertLink = $pdo->prepare('INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id) VALUES (:tenant,:entity_type,:entity_id,:tag_id) ON CONFLICT DO NOTHING');

    foreach (($fixture['tags'] ?? []) as $tag) {
        $insertTag->execute([
            ':id' => $makeId((string) ($tag['slug'] ?? '')),
            ':tenant' => $tenant,
            ':slug' => (string) ($tag['slug'] ?? ''),
            ':name' => (string) ($tag['name'] ?? ''),
            ':locale' => (string) ($tag['locale'] ?? 'en'),
            ':weight' => (int) ($tag['weight'] ?? 0),
        ]);
        ++$tagCount;
    }

    foreach (($fixture['links'] ?? []) as $link) {
        $findId->execute([
            ':tenant' => $tenant,
            ':slug' => (string) ($link['slug'] ?? ''),
        ]);
        $tagId = $findId->fetchColumn();
        if (!is_string($tagId) || $tagId === '') {
            throw new RuntimeException('fixture_dry_run_missing_tag');
        }

        $insertLink->execute([
            ':tenant' => $tenant,
            ':entity_type' => (string) ($link['entity_type'] ?? ''),
            ':entity_id' => (string) ($link['entity_id'] ?? ''),
            ':tag_id' => $tagId,
        ]);
        ++$linkCount;
    }

    $pdo->rollBack();

    fwrite(STDOUT, json_encode([
        'ok' => true,
        'tenant' => $tenant,
        'tag_count' => $tagCount,
        'link_count' => $linkCount,
        'rolled_back' => true,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
