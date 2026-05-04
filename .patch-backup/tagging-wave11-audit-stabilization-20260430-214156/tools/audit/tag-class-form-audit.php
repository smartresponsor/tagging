<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$renamed = [
    'src/Application/Write/Tag/UseCase/CreateTag.php' => 'src/Application/Write/Tag/UseCase/TagCreateUseCase.php',
    'src/Application/Write/Tag/UseCase/CreateTagInterface.php' => 'src/Application/Write/Tag/UseCase/TagCreateUseCaseInterface.php',
    'src/Application/Write/Tag/UseCase/DeleteTag.php' => 'src/Application/Write/Tag/UseCase/TagDeleteUseCase.php',
    'src/Application/Write/Tag/UseCase/DeleteTagInterface.php' => 'src/Application/Write/Tag/UseCase/TagDeleteUseCaseInterface.php',
    'src/Application/Write/Tag/UseCase/PatchTag.php' => 'src/Application/Write/Tag/UseCase/TagPatchUseCase.php',
    'src/Application/Write/Tag/UseCase/PatchTagInterface.php' => 'src/Application/Write/Tag/UseCase/TagPatchUseCaseInterface.php',
    'src/Cache/Store/Tag/SearchCache.php' => 'src/Cache/Store/Tag/TagSearchCache.php',
    'src/Cache/Store/Tag/SuggestCache.php' => 'src/Cache/Store/Tag/TagSuggestCache.php',
    'src/Http/Api/Tag/Middleware/Authorize.php' => 'src/Http/Api/Tag/Middleware/TagAuthorizeMiddleware.php',
    'src/Http/Api/Tag/Middleware/Observe.php' => 'src/Http/Api/Tag/Middleware/TagObserveMiddleware.php',
    'src/Http/Api/Tag/Middleware/QuotaGate.php' => 'src/Http/Api/Tag/Middleware/TagQuotaGateMiddleware.php',
    'src/Http/Api/Tag/Middleware/TenantContext.php' => 'src/Http/Api/Tag/Middleware/TagTenantContextMiddleware.php',
    'src/Http/Api/Tag/Middleware/VerifySignature.php' => 'src/Http/Api/Tag/Middleware/TagVerifySignatureMiddleware.php',
    'src/Http/Api/Tag/Responder/JsonResponder.php' => 'src/Http/Api/Tag/Responder/TagJsonResponder.php',
    'src/Infrastructure/Outbox/Tag/OutboxPublisher.php' => 'src/Infrastructure/Outbox/Tag/TagOutboxPublisher.php',
    'src/Ops/Metrics/PrometheusExporter.php' => 'src/Ops/Metrics/TagPrometheusExporter.php',
    'src/Ops/Security/NonceStore.php' => 'src/Ops/Security/TagNonceStore.php',
    'src/Service/Security/HmacV2Verifier.php' => 'src/Service/Security/TagHmacV2Verifier.php',
];

$violations = [];

foreach ($renamed as $legacy => $canonical) {
    if (file_exists($root . DIRECTORY_SEPARATOR . $legacy)) {
        $violations[] = sprintf('Legacy non-canonical class path still exists: %s', $legacy);
    }

    if (!file_exists($root . DIRECTORY_SEPARATOR . $canonical)) {
        $violations[] = sprintf('Canonical class path is missing: %s', $canonical);
    }
}

$forbiddenSymbols = [
    'App\\Tagging\\Http\\Api\\Tag\\Middleware\\Authorize',
    'App\\Tagging\\Http\\Api\\Tag\\Middleware\\Observe',
    'App\\Tagging\\Http\\Api\\Tag\\Middleware\\QuotaGate',
    'App\\Tagging\\Http\\Api\\Tag\\Middleware\\TenantContext',
    'App\\Tagging\\Http\\Api\\Tag\\Middleware\\VerifySignature',
    'App\\Tagging\\Http\\Api\\Tag\\Responder\\JsonResponder',
    'App\\Tagging\\Infrastructure\\Outbox\\Tag\\OutboxPublisher',
    'App\\Tagging\\Ops\\Metrics\\PrometheusExporter',
    'App\\Tagging\\Ops\\Security\\NonceStore',
    'App\\Tagging\\Service\\Security\\HmacV2Verifier',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $item) {
    if (!$item->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $item->getPathname());
    if (str_contains($path, '/vendor/') || str_contains($path, '/node_modules/')) {
        continue;
    }

    $extension = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($extension, ['php', 'yaml', 'yml', 'xml', 'md', 'json', 'neon'], true)) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    foreach ($forbiddenSymbols as $symbol) {
        if (str_contains($content, $symbol)) {
            $violations[] = sprintf('Legacy class symbol %s remains in %s', $symbol, substr($path, strlen($root) + 1));
        }
    }

    if (str_contains($content, 'namespace App\\Tagging\\') === false && str_starts_with(substr($path, strlen($root) + 1), 'src/')) {
        $violations[] = sprintf('Source file does not keep scoped component namespace App\\Tagging: %s', substr($path, strlen($root) + 1));
    }
}

sort($violations);
$violations = array_values(array_unique($violations));

if ($violations !== []) {
    fwrite(STDERR, "Tag class form audit failed.\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }
    exit(1);
}

fwrite(STDOUT, "Tag class form audit passed.\n");
