<?php

// Copyright (c) 2026 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

use App\HostMinimal\Container\HostMinimalContainer;
use App\HostMinimal\Container\HostMinimalRuntimeConfig;
use App\Http\Api\Tag\AssignController;
use App\Http\Api\Tag\AssignmentReadController;
use App\Http\Api\Tag\SearchController;
use App\Http\Api\Tag\StatusController;
use App\Http\Api\Tag\SuggestController;
use App\Http\Api\Tag\SurfaceController;
use App\Http\Api\Tag\TagController;
use App\Http\Api\Tag\TagWebhookController;
use App\Http\Api\Tag\Middleware\Observe;
use App\Http\Api\Tag\Middleware\TagMiddlewarePipeline;
use App\Http\Api\Tag\Middleware\VerifySignature;
use App\Http\Api\Tag\Responder\TagMiddlewareResponder;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Kernel;
use App\Ops\Security\NonceStore;
use App\Service\Security\HmacV2Verifier;

require_once __DIR__ . '/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

/**
 * Transitional compatibility bootstrap.
 *
 * The Symfony container is the primary composition root. This bridge keeps
 * legacy host-minimal export keys alive while delegating controller graphs to
 * Symfony-managed services.
 *
 * @return array<string, callable(): mixed>
 */
return (static function (): array {
    $cfg = HostMinimalRuntimeConfig::fromGlobals();
    $container = new HostMinimalContainer();
    $kernel = new Kernel(
        $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev',
        (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? true),
    );
    $kernel->boot();
    $symfony = $kernel->getContainer();

    $container->value('runtime', $cfg->runtime);
    $container->value('defaultTenant', $cfg->defaultTenant);
    $container->value('webhookConfig', $cfg->webhook);
    $container->value('observabilityConfig', $cfg->observability);
    $container->value('securityConfig', $cfg->security);

    $container->share('idempotencyMiddleware', static fn(): IdempotencyMiddleware => new IdempotencyMiddleware());
    $container->share('observeMiddleware', static fn(): Observe => new Observe($cfg->observability));
    $container->share('nonceStore', static fn(): NonceStore => new NonceStore(
        $cfg->security['nonce_dir'] ?? 'var/cache/nonce',
        $cfg->security['nonce_ttl_sec'] ?? 300,
        $cfg->security['max_entries'] ?? 100000,
    ));
    $container->share('signatureVerifier', static fn(): HmacV2Verifier => new HmacV2Verifier(
        $cfg->security['secret'] ?? '',
        $cfg->security['skew_sec'] ?? 120,
        $container->get('nonceStore'),
    ));
    $container->share('verifySignatureMiddleware', static fn(): VerifySignature => new VerifySignature(
        $container->get('signatureVerifier'),
        $cfg->security,
        new TagMiddlewareResponder(),
    ));
    $container->share('httpPipeline', static fn(): TagMiddlewarePipeline => new TagMiddlewarePipeline([
        $container->get('observeMiddleware'),
        $container->get('verifySignatureMiddleware'),
    ]));

    $container->share('tagController', static fn(): object => $symfony->get(TagController::class));
    $container->share('assignController', static fn(): object => $symfony->get(AssignController::class));
    $container->share('searchController', static fn(): object => $symfony->get(SearchController::class));
    $container->share('suggestController', static fn(): object => $symfony->get(SuggestController::class));
    $container->share('assignmentReadController', static fn(): object => $symfony->get(AssignmentReadController::class));
    $container->share('statusController', static fn(): object => $symfony->get(StatusController::class));
    $container->share('surfaceController', static fn(): object => $symfony->get(SurfaceController::class));
    $container->share('webhookController', static fn(): object => $symfony->get(TagWebhookController::class));

    return $container->export([
        'runtime',
        'defaultTenant',
        'idempotencyMiddleware',
        'observeMiddleware',
        'verifySignatureMiddleware',
        'httpPipeline',
        'tagController',
        'assignController',
        'searchController',
        'suggestController',
        'assignmentReadController',
        'statusController',
        'surfaceController',
        'webhookController',
    ]);
})();
