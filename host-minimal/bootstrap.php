<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use App\Application\Write\Tag\UseCase\{CreateTag, DeleteTag, PatchTag};
use App\Cache\Store\Tag\{SearchCache, SuggestCache, TagQueryCacheInvalidator};
use App\HostMinimal\Container\{HostMinimalContainer, HostMinimalRuntimeConfig};
use App\Http\Api\Tag\{AssignController,
    AssignmentReadController,
    SearchController,
    StatusController,
    SuggestController,
    SurfaceController,
    TagController,
    TagWebhookController};
use App\Http\Api\Tag\Middleware\Observe;
use App\Http\Api\Tag\Middleware\TagMiddlewarePipeline;
use App\Http\Api\Tag\Middleware\VerifySignature;
use App\Http\Api\Tag\Responder\TagMiddlewareResponder;
use App\Ops\Security\NonceStore;
use App\Service\Security\HmacV2Verifier;
use App\Http\Api\Tag\Responder\TagWebhookResponder;
use App\Http\Api\Tag\Responder\TagWriteResponder;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Infrastructure\Outbox\Tag\OutboxPublisher;
use App\Infrastructure\Persistence\Tag\PdoTagEntityRepository;
use App\Infrastructure\ReadModel\Tag\TagReadModel;
use App\Service\Core\Tag\Audit\TagAuditEmitter;
use App\Service\Core\Tag\Webhook\{TagWebhookRegistry, TagWebhookSender};
use App\Service\Core\Tag\{AssignService,
    IdempotencyStore,
    PdoTransactionRunner,
    SearchService,
    SuggestService,
    TagEntityService,
    UnassignService};
use App\Service\Core\Tag\Slug\{Slugifier, SlugPolicy};

require_once __DIR__ . '/autoload.php';

/**
 * @return array<string, callable(): mixed>
 */
return (static function (): array {
    $cfg = HostMinimalRuntimeConfig::fromGlobals();
    $container = new HostMinimalContainer();
    $pdoOptions = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC];

    $container->value('runtime', $cfg->runtime);
    $container->value('defaultTenant', $cfg->defaultTenant);
    $container->value('webhookConfig', $cfg->webhook);
    $container->value('observabilityConfig', $cfg->observability);
    $container->value('securityConfig', $cfg->security);

    $container->share('pdo', static fn(): \PDO => new \PDO($cfg->dbDsn, $cfg->dbUser, $cfg->dbPass, $pdoOptions));
    $container->share('searchCache', static fn(): SearchCache => new SearchCache());
    $container->share('suggestCache', static fn(): SuggestCache => new SuggestCache());
    $container->share('queryCacheInvalidator', static fn(): TagQueryCacheInvalidator => new TagQueryCacheInvalidator(
        $container->get('searchCache'),
        $container->get('suggestCache'),
    ));
    $container->share('idempotencyMiddleware', static fn(): IdempotencyMiddleware => new IdempotencyMiddleware());
    $container->share('observeMiddleware', static fn(): Observe => new Observe($container->get('observabilityConfig')));

    $container->share('nonceStore', static fn(): NonceStore => new NonceStore(
        (string) ($container->get('securityConfig')['nonce_dir'] ?? 'var/cache/nonce'),
        (int) ($container->get('securityConfig')['nonce_ttl_sec'] ?? 300),
        (int) ($container->get('securityConfig')['max_entries'] ?? 100000),
    ));
    $container->share('signatureVerifier', static fn(): HmacV2Verifier => new HmacV2Verifier(
        (string) ($container->get('securityConfig')['secret'] ?? ''),
        (int) ($container->get('securityConfig')['skew_sec'] ?? 120),
        $container->get('nonceStore'),
    ));
    $container->share('verifySignatureMiddleware', static fn(): VerifySignature => new VerifySignature(
        $container->get('signatureVerifier'),
        $container->get('securityConfig'),
        new TagMiddlewareResponder(),
    ));
    $container->share('httpPipeline', static fn(): TagMiddlewarePipeline => new TagMiddlewarePipeline([
        $container->get('observeMiddleware'),
        $container->get('verifySignatureMiddleware'),
    ]));
    $container->share('statusController', static fn(): StatusController => new StatusController(
        static fn(): bool => (bool) $container->get('pdo')->query('SELECT 1')->fetchColumn(),
        $cfg->runtimeVersion,
        null,
        $cfg->runtime,
    ));
    $container->share('surfaceController', static fn(): SurfaceController => new SurfaceController($cfg->runtime));
    $container->share('tagReadModel', static fn(): TagReadModel => new TagReadModel($container->get('pdo')));
    $container->share('webhookRegistry', static fn(): TagWebhookRegistry => new TagWebhookRegistry((string) ($container->get('webhookConfig')['registry_path'] ?? 'report/webhook/registry.json')));
    $container->share('webhookSender', static fn(): TagWebhookSender => new TagWebhookSender($container->get('webhookConfig')));
    $container->share('auditEmitter', static fn(): TagAuditEmitter => new TagAuditEmitter($container->get('webhookConfig'), $container->get('webhookSender')));
    $container->share('webhookController', static fn(): TagWebhookController => new TagWebhookController(
        $container->get('webhookRegistry'),
        $container->get('auditEmitter'),
        new TagWebhookResponder(),
    ));

    $container->share('tagController', static function () use ($container): TagController {
        $slugifier = new Slugifier();
        $slugPolicy = new SlugPolicy($container->get('pdo'), $slugifier);
        $tagRepo = new PdoTagEntityRepository($container->get('pdo'));
        $tx = new PdoTransactionRunner($container->get('pdo'));
        $tagSvc = new TagEntityService($tagRepo, $slugPolicy);

        return new TagController(
            $tagSvc,
            new CreateTag($tagRepo, $slugPolicy, $tx, $container->get('searchCache'), $container->get('suggestCache'), $container->get('queryCacheInvalidator')),
            new PatchTag($tagRepo, $tx, $container->get('searchCache'), $container->get('suggestCache'), $container->get('queryCacheInvalidator')),
            new DeleteTag($tagRepo, $tx, $container->get('searchCache'), $container->get('suggestCache'), $container->get('queryCacheInvalidator')),
            new TagWriteResponder(),
        );
    });

    $container->share('assignController', static function () use ($container, $cfg): AssignController {
        $outbox = new OutboxPublisher($container->get('pdo'));
        $idemStore = new IdempotencyStore($container->get('pdo'));

        return new AssignController(
            new AssignService($container->get('pdo'), $outbox, $idemStore),
            new UnassignService($container->get('pdo'), $outbox, $idemStore),
            ['entity_types' => $cfg->entityTypes],
        );
    });

    $container->share('searchController', static fn(): SearchController => new SearchController(
        new SearchService($container->get('tagReadModel'), $container->get('searchCache')),
    ));
    $container->share('suggestController', static fn(): SuggestController => new SuggestController(
        new SuggestService($container->get('tagReadModel'), $container->get('suggestCache')),
    ));
    $container->share('assignmentReadController', static fn(): AssignmentReadController => new AssignmentReadController($container->get('tagReadModel')));

    return $container->export([
        'runtime',
        'idempotencyMiddleware',
        'observeMiddleware',
        'verifySignatureMiddleware',
        'httpPipeline',
        'statusController',
        'surfaceController',
        'tagController',
        'assignController',
        'searchController',
        'suggestController',
        'assignmentReadController',
        'webhookController',
        'defaultTenant',
    ]);
})();
