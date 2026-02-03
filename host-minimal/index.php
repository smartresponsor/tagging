<?php
declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
require_once __DIR__.'/../vendor/autoload.php';

use App\Cache\Tag\{SearchCache,SuggestCache};
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Tag\{AssignController,AssignmentReadController,RedirectController,SearchController,StatusController,SuggestController,SynonymController,TagController};
use App\Infra\Outbox\OutboxPublisher;
use App\Infra\Tag\TagReadModel;
use App\Service\Tag\{AssignService,IdempotencyStore,SearchService,SuggestService,UnassignService};
use App\Service\Tag\Slug\Slugifier;

$pdo = new PDO(
    getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app',
    getenv('DB_USER') ?: 'app',
    getenv('DB_PASS') ?: 'app',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
);

$mw = new IdempotencyMiddleware();
$norm = $mw->normalize($_SERVER, $_GET, file_get_contents('php://input') ?: '');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$slugifier = new Slugifier();
$tagCtl = new TagController($pdo, $slugifier);

$readModel = new TagReadModel($pdo);

$outbox = new OutboxPublisher($pdo);
$idemStore = new IdempotencyStore($pdo);
$assignSvc = new AssignService($pdo, $outbox, $idemStore);
$unassignSvc = new UnassignService($pdo, $outbox, $idemStore);
$typesEnv = getenv('TAG_ENTITY_TYPES') ?: '*';
$types = array_values(array_filter(array_map('trim', explode(',', $typesEnv)), static fn($v) => $v !== ''));
if ($types === []) $types = ['*'];

$assignCtl = new AssignController($assignSvc, $unassignSvc, ['entity_types'=>$types]);

$searchSvc = new SearchService($readModel, new SearchCache());
$suggestSvc = new SuggestService($pdo, new SuggestCache());
$searchCtl = new SearchController($searchSvc);
$suggestCtl = new SuggestController($suggestSvc);

$assignmentReadCtl = new AssignmentReadController($readModel);

// Framework-agnostic controllers for file-based features
$synonymCtl = new SynonymController();
$redirectCtl = new RedirectController();
$statusCtl = new StatusController();

/** @param array{0:int,1:array<string,string>,2:string} $r */
$send = static function(array $r): void {
    [$c,$h,$b] = $r;
    http_response_code($c);
    foreach($h as $k=>$v){ header($k.': '.$v); }
    echo $b;
    exit;
};

// Tag CRUD
if ($method === 'POST' && $path === '/tag') {
    $send($tagCtl->create($norm));
}
if ($method === 'GET' && preg_match('#^/tag/([A-Za-z0-9]{26})$#', $path, $m)) {
    $send($tagCtl->get($norm, $m[1]));
}
if ($method === 'PATCH' && preg_match('#^/tag/([A-Za-z0-9]{26})$#', $path, $m)) {
    $send($tagCtl->patch($norm, $m[1]));
}
if ($method === 'DELETE' && preg_match('#^/tag/([A-Za-z0-9]{26})$#', $path, $m)) {
    $send($tagCtl->delete($norm, $m[1]));
}

// Assign / unassign
if ($method === 'POST' && preg_match('#^/tag/([A-Za-z0-9]{26})/assign$#', $path, $m)) {
    $send($assignCtl->assign($norm, $m[1]));
}
if ($method === 'POST' && preg_match('#^/tag/([A-Za-z0-9]{26})/unassign$#', $path, $m)) {
    $send($assignCtl->unassign($norm, $m[1]));
}

// Bulk operations + bulk assign-to-entity
if ($method === 'POST' && $path === '/tag/assign-bulk') {
    $send($assignCtl->bulk($norm));
}
if ($method === 'POST' && $path === '/tag/assignment/bulk') {
    $send($assignCtl->assignBulkToEntity($norm));
}

// Read assignments
if ($method === 'GET' && $path === '/tag/assignments') {
    $send($assignmentReadCtl->listByEntity($norm));
}

// Search/suggest
if ($method === 'GET' && $path === '/tag/search') {
    $send($searchCtl->get($norm));
}
if ($method === 'GET' && $path === '/tag/suggest') {
    $send($suggestCtl->get($norm));
}

// Synonyms
if ($method === 'GET' && preg_match('#^/tag/([A-Za-z0-9]{26})/synonym$#', $path, $m)) {
    $send($synonymCtl->list(['id'=>$m[1]]));
}
if ($method === 'POST' && preg_match('#^/tag/([A-Za-z0-9]{26})/synonym$#', $path, $m)) {
    $send($synonymCtl->add(['id'=>$m[1]], is_array($norm['body'] ?? null) ? $norm['body'] : []));
}
if ($method === 'DELETE' && preg_match('#^/tag/([A-Za-z0-9]{26})/synonym$#', $path, $m)) {
    $send($synonymCtl->remove(['id'=>$m[1]], is_array($norm['body'] ?? null) ? $norm['body'] : []));
}

// Redirect resolve
if ($method === 'GET' && preg_match('#^/tag/redirect/([A-Za-z0-9]{26})$#', $path, $m)) {
    $send($redirectCtl->resolve(['fromId'=>$m[1]]));
}

// Status
if ($method === 'GET' && $path === '/tag/_status') {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($statusCtl->status(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['code'=>'not_found']);
