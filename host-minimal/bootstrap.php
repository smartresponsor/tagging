<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use App\Application\Tag\UseCase\{CreateTag, DeleteTag, PatchTag};
use App\Cache\Tag\{SearchCache, SuggestCache};
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Tag\{AssignController,
    AssignmentReadController,
    RedirectController,
    SearchController,
    StatusController,
    SuggestController,
    SynonymController,
    TagController};
use App\Http\Tag\Responder\TagWriteResponder;
use App\Infra\Outbox\OutboxPublisher;
use App\Infra\Tag\{PdoTagEntityRepository, TagReadModel};
use App\Service\Tag\{AssignService,
    IdempotencyStore,
    PdoTransactionRunner,
    SearchService,
    SuggestService,
    TagEntityService,
    UnassignService};
use App\Service\Tag\Slug\{Slugifier, SlugPolicy};

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @return array<string, callable(): mixed>
 */
return (static function (): array {
    /**
     * @template T
     * @param callable():T $factory
     * @return callable():T
     */
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

    $pdo = $shared(static fn(): PDO => new PDO(
        getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app',
        getenv('DB_USER') ?: 'app',
        getenv('DB_PASS') ?: 'app',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    ));

    $idempotencyMiddleware = $shared(static fn(): IdempotencyMiddleware => new IdempotencyMiddleware());
    $statusController = $shared(static fn(): StatusController => new StatusController());

    $tagController = $shared(static function () use ($pdo): TagController {
        $slugifier = new Slugifier();
        $slugPolicy = new SlugPolicy($pdo(), $slugifier);
        $tagRepo = new PdoTagEntityRepository($pdo());
        $tx = new PdoTransactionRunner($pdo());
        $tagSvc = new TagEntityService($tagRepo, $slugPolicy);
        $createTag = new CreateTag($tagRepo, $slugPolicy, $tx);
        $patchTag = new PatchTag($tagRepo, $tx);
        $deleteTag = new DeleteTag($tagRepo, $tx);

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

    $searchController = $shared(static function () use ($tagReadModel): SearchController {
        $searchSvc = new SearchService($tagReadModel(), new SearchCache());
        return new SearchController($searchSvc);
    });

    $suggestController = $shared(static function () use ($pdo): SuggestController {
        $suggestSvc = new SuggestService($pdo(), new SuggestCache());
        return new SuggestController($suggestSvc);
    });

    $assignmentReadController = $shared(static fn(): AssignmentReadController => new AssignmentReadController($tagReadModel()));
    $synonymController = $shared(static fn(): SynonymController => new SynonymController());
    $redirectController = $shared(static fn(): RedirectController => new RedirectController());

    return [
        'idempotencyMiddleware' => $idempotencyMiddleware,
        'statusController' => $statusController,
        'tagController' => $tagController,
        'assignController' => $assignController,
        'searchController' => $searchController,
        'suggestController' => $suggestController,
        'assignmentReadController' => $assignmentReadController,
        'synonymController' => $synonymController,
        'redirectController' => $redirectController,
    ];
})();
