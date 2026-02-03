<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'app';
$tenant = getenv('TENANT') ?: 'demo';
$tagFile = __DIR__ . '/../../seed/tag/tag-demo.ndjson';
$linkFile = __DIR__ . '/../../seed/tag/tag-links-demo.ndjson';

$pdo = new PDO($dsn, $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec('BEGIN');
$insTag = $pdo->prepare("INSERT INTO tag_entity (id, tenant, slug, name, locale, weight)
 VALUES (:id,:tenant,:slug,:name,:locale,:weight)
 ON CONFLICT (tenant, slug) DO UPDATE SET name=EXCLUDED.name, locale=EXCLUDED.locale, weight=EXCLUDED.weight");
$insLink = $pdo->prepare("INSERT INTO tag_link (tenant, entity_type, entity_id, tag_id)
 VALUES (:tenant,:entity_type,:entity_id,:tag_id)
 ON CONFLICT (tenant, entity_type, entity_id, tag_id) DO NOTHING");

$tags = 0; $links = 0;
$fh = fopen($tagFile, 'r'); if (!$fh) { throw new RuntimeException('Cannot open tag-demo.ndjson'); }
while (($line = fgets($fh)) !== false) {
  $o = json_decode($line, true); if (!$o) continue;
  $insTag->execute([
    ':id'=>$o['id'], ':tenant'=>$tenant, ':slug'=>$o['slug'], ':name'=>$o['name'],
    ':locale'=>$o['locale'], ':weight'=>(int)$o['weight'],
  ]);
  $tags++;
}
fclose($fh);

$fh = fopen($linkFile, 'r'); if (!$fh) { throw new RuntimeException('Cannot open tag-links-demo.ndjson'); }
while (($line = fgets($fh)) !== false) {
  $o = json_decode($line, true); if (!$o) continue;
  $insLink->execute([
    ':tenant'=>$tenant, ':entity_type'=>$o['entity_type'], ':entity_id'=>$o['entity_id'], ':tag_id'=>$o['tag_id'],
  ]);
  $links++;
}
fclose($fh);
$pdo->exec('COMMIT');

echo json_encode(['ok'=>true,'tags'=>$tags,'links'=>$links,'tenant'=>$tenant]) . PHP_EOL;
