<?php

declare(strict_types=1);

/**
 * Ensures the Tagging repository no longer contains explicitly retired duplicate
 * implementation surfaces after the class-form, service-depth, persistence, test,
 * and tooling cleanup waves.
 */
$repoRoot = dirname(__DIR__, 2);

$composerPath = $repoRoot . '/composer.json';
if (!is_file($composerPath)) {
    fwrite(STDERR, "Missing composer.json\n");

    exit(1);
}

$composer = (string) file_get_contents($composerPath);
if (!str_contains($composer, 'App\\\\Tagging\\\\')) {
    fwrite(STDERR, "composer.json must keep App\\Tagging\\ as the component namespace.\n");

    exit(1);
}

$forbidden = [
    'Tagging',
    'tag-compose.yaml',
    'host/Dockerfile',
    'src/Application/Write/Tag/Dto/CreateTagCommand.php',
    'src/Application/Write/Tag/Dto/DeleteTagCommand.php',
    'src/Application/Write/Tag/Dto/PatchTagCommand.php',
    'src/Application/Write/Tag/UseCase/CreateTag.php',
    'src/Application/Write/Tag/UseCase/CreateTagInterface.php',
    'src/Application/Write/Tag/UseCase/DeleteTag.php',
    'src/Application/Write/Tag/UseCase/DeleteTagInterface.php',
    'src/Application/Write/Tag/UseCase/PatchTag.php',
    'src/Application/Write/Tag/UseCase/PatchTagInterface.php',
    'src/Cache/Store/Tag/SearchCache.php',
    'src/Cache/Store/Tag/SuggestCache.php',
    'src/Data/Model/Tag/AssignmentRecord.php',
    'src/Entity/Core/Tag/IdempotencyStore.php',
    'src/Entity/Core/Tag/OutboxEvent.php',
    'src/Http/Api/Tag/AssignController.php',
    'src/Http/Api/Tag/AssignmentReadController.php',
    'src/Http/Api/Tag/CorsHeaders.php',
    'src/Http/Api/Tag/MetricsController.php',
    'src/Http/Api/Tag/Middleware/Authorize.php',
    'src/Http/Api/Tag/Middleware/Observe.php',
    'src/Http/Api/Tag/Middleware/QuotaGate.php',
    'src/Http/Api/Tag/Middleware/TenantContext.php',
    'src/Http/Api/Tag/Middleware/VerifySignature.php',
    'src/Http/Api/Tag/Responder/JsonResponder.php',
    'src/Http/Api/Tag/RuntimeSurfaceCatalog.php',
    'src/Http/Api/Tag/RuntimeVersion.php',
    'src/Http/Api/Tag/SearchController.php',
    'src/Http/Api/Tag/StatusController.php',
    'src/Http/Api/Tag/SuggestController.php',
    'src/Http/Api/Tag/SurfaceController.php',
    'src/Http/Middleware/IdempotencyMiddleware.php',
    'src/Infrastructure/Outbox/Tag/OutboxPublisher.php',
    'src/Infrastructure/Persistence/Tag/DoctrineTagEntityRepository.php',
    'src/Infrastructure/Persistence/Tag/DoctrineTagRepository.php',
    'src/Infrastructure/Persistence/Tag/InMemoryTagRepository.php',
    'src/Ops/Metrics/PrometheusExporter.php',
    'src/Ops/Security/NonceStore.php',
    'src/Service/Authz/Tag/TagAuthorizer.php',
    'src/Service/Authz/TagAuthorizer.php',
    'src/Service/Slug/Tag/SlugPolicy.php',
    'src/Service/Slug/Tag/Slugifier.php',
    'src/Service/Slug/Tag/TagSlugPolicy.php',
    'src/Service/Slug/Tag/TagSlugifier.php',
    'src/Service/Core/Tag/AssignOperationInterface.php',
    'src/Service/Core/Tag/AssignService.php',
    'src/Service/Core/Tag/Audit/TagAuditEmitter.php',
    'src/Service/Core/Tag/Authz/TagAuthorizer.php',
    'src/Service/Core/Tag/Cache/TagCache.php',
    'src/Service/Core/Tag/CallableTagErrorSink.php',
    'src/Service/Core/Tag/DoctrineTransactionRunner.php',
    'src/Service/Core/Tag/IdempotencyStore.php',
    'src/Service/Core/Tag/Metric/TagMetrics.php',
    'src/Service/Core/Tag/NullTagErrorSink.php',
    'src/Service/Core/Tag/Quota/TagQuota.php',
    'src/Service/Core/Tag/QuotaService.php',
    'src/Service/Core/Tag/RateLimiter.php',
    'src/Service/Core/Tag/Record/TagAuditRecord.php',
    'src/Service/Core/Tag/Record/TagClassificationRecord.php',
    'src/Service/Core/Tag/Record/TagEffectRecord.php',
    'src/Service/Core/Tag/Record/TagEntityCreateRecord.php',
    'src/Service/Core/Tag/SearchService.php',
    'src/Service/Core/Tag/Slug/SlugPolicy.php',
    'src/Service/Core/Tag/Slug/Slugifier.php',
    'src/Service/Core/Tag/Slug/TagSlugPolicy.php',
    'src/Service/Core/Tag/Slug/TagSlugifier.php',
    'src/Service/Core/Tag/SuggestService.php',
    'src/Service/Core/Tag/TenantGuard.php',
    'src/Service/Core/Tag/TransactionRunnerInterface.php',
    'src/Service/Core/Tag/UlidGenerator.php',
    'src/Service/Core/Tag/UnassignOperationInterface.php',
    'src/Service/Core/Tag/UnassignService.php',
    'src/Service/Core/Tag/Webhook/TagWebhookRegistry.php',
    'src/Service/Core/Tag/Webhook/TagWebhookSender.php',
    'tools/lint.php',
    'tools/git/install-hooks.php',
    'tools/local/panther-test.sh',
    'tools/local/php-extension-doctor.sh',
    'tools/db/migrate.php',
    'tools/smoke/tag_tag-smoke.sh',
];

$violations = [];
foreach ($forbidden as $relativePath) {
    $absolutePath = $repoRoot . '/' . $relativePath;
    if (file_exists($absolutePath)) {
        $violations[] = $relativePath;
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Legacy duplicate residue still exists:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging duplicate residue audit passed.\n";
