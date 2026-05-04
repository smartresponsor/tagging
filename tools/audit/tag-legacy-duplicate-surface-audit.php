<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$legacyFiles = [
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
    'src/Ops/Metrics/PrometheusExporter.php',
    'src/Ops/Security/NonceStore.php',
    'src/Service/Authz/Tag/TagAuthorizer.php',
    'src/Service/Authz/TagAuthorizer.php',
    'src/Service/Core/Tag/AssignOperationInterface.php',
    'src/Service/Core/Tag/AssignService.php',
    'src/Service/Core/Tag/CallableTagErrorSink.php',
    'src/Service/Core/Tag/DoctrineTransactionRunner.php',
    'src/Service/Core/Tag/IdempotencyStore.php',
    'src/Service/Core/Tag/NullTagErrorSink.php',
    'src/Service/Core/Tag/QuotaService.php',
    'src/Service/Core/Tag/RateLimiter.php',
    'src/Service/Core/Tag/SearchService.php',
    'src/Service/Core/Tag/SuggestService.php',
    'src/Service/Core/Tag/TenantGuard.php',
    'src/Service/Core/Tag/TransactionRunnerInterface.php',
    'src/Service/Core/Tag/UlidGenerator.php',
    'src/Service/Core/Tag/UnassignOperationInterface.php',
    'src/Service/Core/Tag/UnassignService.php',
    'src/Service/Core/Tag/Slug/SlugPolicy.php',
    'src/Service/Core/Tag/Slug/Slugifier.php',
    'src/Service/Slug/Tag/SlugPolicy.php',
    'src/Service/Slug/Tag/Slugifier.php',
    'src/Service/Slug/Tag/TagSlugPolicy.php',
    'src/Service/Slug/Tag/TagSlugifier.php',
];

$canonicalFiles = [
    'src/Application/Write/Tag/Dto/TagCreateCommand.php',
    'src/Application/Write/Tag/Dto/TagDeleteCommand.php',
    'src/Application/Write/Tag/Dto/TagPatchCommand.php',
    'src/Application/Write/Tag/UseCase/TagCreateUseCase.php',
    'src/Application/Write/Tag/UseCase/TagDeleteUseCase.php',
    'src/Application/Write/Tag/UseCase/TagPatchUseCase.php',
    'src/Cache/Store/Tag/TagSearchCache.php',
    'src/Cache/Store/Tag/TagSuggestCache.php',
    'src/Data/Model/Tag/TagAssignmentRecord.php',
    'src/Entity/Core/Tag/TagIdempotencyStore.php',
    'src/Entity/Core/Tag/TagOutboxEvent.php',
    'src/Http/Api/Tag/TagAssignController.php',
    'src/Http/Api/Tag/TagAssignmentReadController.php',
    'src/Http/Api/Tag/TagCorsHeaders.php',
    'src/Http/Api/Tag/TagMetricsController.php',
    'src/Http/Api/Tag/Middleware/TagAuthorizeMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagObserveMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagQuotaGateMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagTenantContextMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagVerifySignatureMiddleware.php',
    'src/Http/Api/Tag/Responder/TagJsonResponder.php',
    'src/Http/Api/Tag/TagRuntimeSurfaceCatalog.php',
    'src/Http/Api/Tag/TagRuntimeVersion.php',
    'src/Http/Api/Tag/TagSearchController.php',
    'src/Http/Api/Tag/TagStatusController.php',
    'src/Http/Api/Tag/TagSuggestController.php',
    'src/Http/Api/Tag/TagSurfaceController.php',
    'src/Http/Middleware/TagIdempotencyMiddleware.php',
    'src/Infrastructure/Outbox/Tag/TagOutboxPublisher.php',
    'src/Ops/Metrics/TagPrometheusExporter.php',
    'src/Ops/Security/TagNonceStore.php',
    'src/Service/Core/TagAssignOperationInterface.php',
    'src/Service/Core/TagAssignService.php',
    'src/Service/Core/TagCallableErrorSink.php',
    'src/Service/Core/TagDoctrineTransactionRunner.php',
    'src/Service/Core/TagIdempotencyStore.php',
    'src/Service/Core/TagNullErrorSink.php',
    'src/Service/Core/TagQuotaService.php',
    'src/Service/Core/TagRateLimiter.php',
    'src/Service/Core/TagSearchService.php',
    'src/Service/Core/TagSuggestService.php',
    'src/Service/Core/TagTenantGuard.php',
    'src/Service/Core/TagTransactionRunnerInterface.php',
    'src/Service/Core/TagUlidGenerator.php',
    'src/Service/Core/TagUnassignOperationInterface.php',
    'src/Service/Core/TagUnassignService.php',
    'src/Service/Core/Slug/TagSlugPolicy.php',
    'src/Service/Core/Slug/TagSlugifier.php',
];

$failures = [];
foreach ($legacyFiles as $relative) {
    if (is_file($root . DIRECTORY_SEPARATOR . $relative)) {
        $failures[] = 'Legacy duplicate file still exists: ' . $relative;
    }
}

foreach ($canonicalFiles as $relative) {
    if (!is_file($root . DIRECTORY_SEPARATOR . $relative)) {
        $failures[] = 'Canonical file is missing: ' . $relative;
    }
}

$composer = $root . DIRECTORY_SEPARATOR . 'composer.json';
if (is_file($composer)) {
    $contents = (string) file_get_contents($composer);
    if (!str_contains($contents, 'App\\\\Tagging\\\\')) {
        $failures[] = 'composer.json must keep the App\\Tagging\\ namespace mapping.';
    }
    if (str_contains($contents, '"App\\\\": "src/"')) {
        $failures[] = 'composer.json must not collapse Tagging to plain App\\ namespace.';
    }
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo 'Tagging legacy duplicate surface audit passed.' . PHP_EOL;
