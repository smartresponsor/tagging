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
    $get = static fn(string $id): mixed => $container->get($id);
    $queryCacheInvalidator = static fn(): TagQueryCacheInvalidator => new TagQueryCacheInvalidator(
        $get('searchCache'),
        $get('suggestCache'),
    );
    $tagWriteResponder = static fn(): TagWriteResponder => new TagWriteResponder();
    $shareConfig = static function () use ($container, $cfg): void {
        $container->value('runtime', $cfg->runtime);
        $container->value('defaultTenant', $cfg->defaultTenant);
        $container->value('webhookConfig', $cfg->webhook);
        $container->value('observabilityConfig', $cfg->observability);
        $container->value('securityConfig', $cfg->security);
    };
    $shareInfrastructure = static function () use ($container, $cfg, $get, $queryCacheInvalidator): void {
        $pdoOptions = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
        $container->share('pdo', static fn(): PDO => new PDO($cfg->dbDsn, $cfg->dbUser, $cfg->dbPass, $pdoOptions));
        $container->share('searchCache', static fn(): SearchCache => new SearchCache());
        $container->share('suggestCache', static fn(): SuggestCache => new SuggestCache());
        $container->share('queryCacheInvalidator', $queryCacheInvalidator);
        $container->share('slugifier', static fn(): Slugifier => new Slugifier());
        $container->share('slugPolicy', static fn(): SlugPolicy => new SlugPolicy($get('pdo'), $get('slugifier')));
        $container->share('tagRepo', static fn(): PdoTagEntityRepository => new PdoTagEntityRepository($get('pdo')));
        $container->share('txRunner', static fn(): PdoTransactionRunner => new PdoTransactionRunner($get('pdo')));
        $container->share('outboxPublisher', static fn(): OutboxPublisher => new OutboxPublisher($get('pdo')));
        $container->share('idempotencyStore', static fn(): IdempotencyStore => new IdempotencyStore($get('pdo')));
        $container->share(
            'tagEntityService',
            static fn(): TagEntityService => new TagEntityService($get('tagRepo'), $get('slugPolicy')),
        );
        $container->share('tagReadModel', static fn(): TagReadModel => new TagReadModel($get('pdo')));
    };
    $shareMiddleware = static function () use ($container, $get): void {
        $container->share('idempotencyMiddleware', static fn(): IdempotencyMiddleware => new IdempotencyMiddleware());
        $container->share('observeMiddleware', static fn(): Observe => new Observe($get('observabilityConfig')));
        $container->share('nonceStore', static fn(): NonceStore => new NonceStore(
            $get('securityConfig')['nonce_dir'] ?? 'var/cache/nonce',
            $get('securityConfig')['nonce_ttl_sec'] ?? 300,
            $get('securityConfig')['max_entries'] ?? 100000,
        ));
        $container->share('signatureVerifier', static fn(): HmacV2Verifier => new HmacV2Verifier(
            $get('securityConfig')['secret'] ?? '',
            $get('securityConfig')['skew_sec'] ?? 120,
            $get('nonceStore'),
        ));
        $container->share('verifySignatureMiddleware', static fn(): VerifySignature => new VerifySignature(
            $get('signatureVerifier'),
            $get('securityConfig'),
            new TagMiddlewareResponder(),
        ));
        $container->share('httpPipeline', static fn(): TagMiddlewarePipeline => new TagMiddlewarePipeline([
            $get('observeMiddleware'),
            $get('verifySignatureMiddleware'),
        ]));
    };
    $shareControllers = static function () use ($container, $cfg, $get, $tagWriteResponder): void {
        $container->share('statusController', static fn(): StatusController => new StatusController(
            static fn(): bool => false !== $get('pdo')->query('SELECT 1')->fetchColumn(),
            $cfg->runtimeVersion,
            null,
            $cfg->runtime,
        ));
        $container->share('surfaceController', static fn(): SurfaceController => new SurfaceController($cfg->runtime));
        $container->share('tagController', static function () use ($get, $tagWriteResponder): TagController {
            return new TagController(
                $get('tagEntityService'),
                new CreateTag(
                    $get('tagRepo'),
                    $get('slugPolicy'),
                    $get('txRunner'),
                    $get('searchCache'),
                    $get('suggestCache'),
                    $get('queryCacheInvalidator'),
                ),
                new PatchTag(
                    $get('tagRepo'),
                    $get('txRunner'),
                    $get('searchCache'),
                    $get('suggestCache'),
                    $get('queryCacheInvalidator'),
                ),
                new DeleteTag(
                    $get('tagRepo'),
                    $get('txRunner'),
                    $get('searchCache'),
                    $get('suggestCache'),
                    $get('queryCacheInvalidator'),
                ),
                $tagWriteResponder(),
            );
        });
        $container->share('assignController', static function () use ($get, $cfg): AssignController {
            return new AssignController(
                new AssignService($get('pdo'), $get('outboxPublisher'), $get('idempotencyStore')),
                new UnassignService($get('pdo'), $get('outboxPublisher'), $get('idempotencyStore')),
                ['entity_types' => $cfg->entityTypes],
            );
        });
        $container->share('searchController', static fn(): SearchController => new SearchController(
            new SearchService($get('tagReadModel'), $get('searchCache')),
        ));
        $container->share('suggestController', static fn(): SuggestController => new SuggestController(
            new SuggestService($get('tagReadModel'), $get('suggestCache')),
        ));
        $container->share(
            'assignmentReadController',
            static fn(): AssignmentReadController => new AssignmentReadController($get('tagReadModel')),
        );
    };
    $shareWebhookServices = static function () use ($container, $get): void {
        $container->share(
            'webhookRegistry',
            static fn(): TagWebhookRegistry => new TagWebhookRegistry(
                $get('webhookConfig')['registry_path'] ?? 'report/webhook/registry.json',
            ),
        );
        $container->share(
            'webhookSender',
            static fn(): TagWebhookSender => new TagWebhookSender($get('webhookConfig')),
        );
        $container->share(
            'auditEmitter',
            static fn(): TagAuditEmitter => new TagAuditEmitter($get('webhookConfig'), $get('webhookSender')),
        );
        $container->share('webhookController', static fn(): TagWebhookController => new TagWebhookController(
            $get('webhookRegistry'),
            $get('auditEmitter'),
            new TagWebhookResponder(),
        ));
    };

    $shareConfig();
    $shareInfrastructure();
    $shareMiddleware();
    $shareControllers();
    $shareWebhookServices();

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
