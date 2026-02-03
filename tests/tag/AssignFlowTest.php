<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * NOTE: This is a minimal integration-style script; adapt for your test framework.
 */
use App\Infra\Outbox\OutboxPublisher;
use App\Service\Tag\{AssignService,UnassignService,IdempotencyStore};

require_once __DIR__ . '/../../vendor/autoload.php';

$pdo = new PDO(getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app', getenv('DB_USER') ?: 'app', getenv('DB_PASS') ?: 'app');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tenant = 'demo';
$tagId = '01HTESTASSIGN00000000000000';
$entityType = 'product';
$entityId = 'p-1001';

// Ensure tag exists
$pdo->exec("INSERT INTO tag_entity (id, tenant, slug, name) VALUES ('$tagId','$tenant','test-assign','Test Assign') ON CONFLICT DO NOTHING");

$outbox = new OutboxPublisher($pdo);
$idem = new IdempotencyStore($pdo);
$assign = new AssignService($pdo, $outbox, $idem);

// First call (new)
$res1 = $assign->assign($tenant, $tagId, $entityType, $entityId, 'idem-1');
// Second call (duplicate)
$res2 = $assign->assign($tenant, $tagId, $entityType, $entityId, 'idem-1');

echo json_encode(['first'=>$res1, 'second'=>$res2], JSON_PRETTY_PRINT) . PHP_EOL;
