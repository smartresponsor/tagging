<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use App\Application\Write\Tag\UseCase\{CreateTag, DeleteTag, PatchTag};
use App\Cache\Store\Tag\{SearchCache, SuggestCache};
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Api\Tag\{AssignController,
    AssignmentReadController,
    SearchController,
    StatusController,
    SuggestController,
    SurfaceController,
    TagController};
use App\Http\Api\Tag\Responder\TagWriteResponder;
use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Infrastructure\Persistence\Tag\PdoTagEntityRepository;
use App\Infrastructure\ReadModel\Tag\TagReadModel;
use App\Service\Core\Tag\{AssignService,
    IdempotencyStore,
    PdoTransactionRunner,
    SearchService,
    SuggestService,
    TagEntityService,
    UnassignService};
use App\Service\Core\Tag\Slug\{Slugifier, SlugPolicy};

require_once __DIR__ . '/autoload.php';

$runtime = require dirname(__DIR__) . '/config/tag_runtime.php';
$runtimeVersion = is_array($runtime) ? (string) ($runtime['version'] ?? 'dev') : 'dev';

/**
 * @return array<string, callable(): mixed>
 */
return (static function () use ($runtime, $runtimeVersion): array {
    $shared = static function (callable $factory): callable {
        $resolved = false;
        $instance = null;

        return static function () use ($factory, &$resolved, &$instance) {
            if ($resolved) {
                return $instance;
            }

            $instance = $factory();
            $resolved = true;
            return $instance;
        };
    };

    $pdoOptions = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC];
    $pdo = $shared(static fn(): \PDO => new \PDO(
        getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app',
        getenv('DB_USER') ?: 'app',
        getenv('DB_PASS') ?: 'app',
        $pdoOptions
    ));

    $searchCache = $shared(static fn(): SearchCache => new SearchCache());
    $suggestCache = $shared(static fn(): SuggestCache => new SuggestCache());
    $idempotencyMiddleware = $shared(static fn(): IdempotencyMiddleware => new IdempotencyMiddleware());
    $statusController = $shared(static fn(): StatusController => new StatusController(
        static fn(): bool => (bool) $pdo()->query('SELECT 1')->fetchColumn(),
        $runtimeVersion,
    ));
    $surfaceController = $shared(static fn(): SurfaceController => new SurfaceController(is_array($runtime) ? $runtime : []));

    $tagController = $shared(static function () use ($pdo, $searchCache, $suggestCache): TagController {
        $slugifier = new Slugifier();
        $slugPolicy = new SlugPolicy($pdo(), $slugifier);
        $tagRepo = new PdoTagEntityRepository($pdo());
        $tx = new PdoTransactionRunner($pdo());
        $tagSvc = new TagEntityService($tagRepo, $slugPolicy);
        $createTag = new CreateTag($tagRepo, $slugPolicy, $tx, $searchCache(), $suggestCache());
        $patchTag = new PatchTag($tagRepo, $tx, $searchCache(), $suggestCache());
        $deleteTag = new DeleteTag($tagRepo, $tx, $searchCache(), $suggestCache());

        return new TagController($tagSvc, $createTag, $patchTag, $deleteTag, new TagWriteResponder());
    });

    $tagReadModel = $shared(static fn(): TagReadModel => new TagReadModel($pdo()));

    $assignController = $shared(static function () use ($pdo): AssignController {
        $outbox = new OutboxPublisher($pdo());
        $idemStore = new IdempotencyStore($pdo());
        $assignSvc = new AssignService($pdo(), $outbox, $idemStore);
        $unassignSvc = new UnassignService($pdo(), $outbox, $idemStore);

        $typesEnv = getenv('TAG_ENTITY_TYPES') ?: '*';
        $types = array_values(array_filter(array_map('trim', explode(',', $typesEnv)), static fn($v): bool => $v !== ''));
        if ($types === []) {
            $types = ['*'];
        }

        return new AssignController($assignSvc, $unassignSvc, ['entity_types' => $types]);
    });

    $searchController = $shared(static function () use ($tagReadModel, $searchCache): SearchController {
        $searchSvc = new SearchService($tagReadModel(), $searchCache());
        return new SearchController($searchSvc);
    });

    $suggestController = $shared(static function () use ($pdo, $suggestCache): SuggestController {
        $suggestSvc = new SuggestService($pdo(), $suggestCache());
        return new SuggestController($suggestSvc);
    });

    $assignmentReadController = $shared(static fn(): AssignmentReadController => new AssignmentReadController($tagReadModel()));

    $defaultTenant = getenv('TENANT') ?: 'demo';

    return [
        'runtime' => static fn(): array => is_array($runtime) ? $runtime : [],
        'idempotencyMiddleware' => $idempotencyMiddleware,
        'statusController' => $statusController,
        'surfaceController' => $surfaceController,
        'tagController' => $tagController,
        'assignController' => $assignController,
        'searchController' => $searchController,
        'suggestController' => $suggestController,
        'assignmentReadController' => $assignmentReadController,
        'defaultTenant' => static fn(): string => $defaultTenant,
    ];
})();
