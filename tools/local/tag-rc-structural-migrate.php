<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$moves = [
    'src/EntityInterface/Contract/TagInterface.php' => 'src/Contract/Entity/TagInterface.php',
    'src/EntityInterface/Contract/TagProductInterface.php' => 'src/Contract/Entity/TagProductInterface.php',
    'src/EntityInterface/Contract/TagProjectInterface.php' => 'src/Contract/Entity/TagProjectInterface.php',
    'src/Middleware/Api/TagAuthorizeMiddleware.php' => 'src/Handler/Http/TagAuthorizeMiddleware.php',
    'src/Middleware/Api/TagIdempotencyMiddleware.php' => 'src/Handler/Http/TagIdempotencyMiddleware.php',
    'src/Middleware/Api/TagMiddlewarePipeline.php' => 'src/Handler/Http/TagMiddlewarePipeline.php',
    'src/Middleware/Api/TagObserveMiddleware.php' => 'src/Handler/Http/TagObserveMiddleware.php',
    'src/Middleware/Api/TagQuotaGateMiddleware.php' => 'src/Handler/Http/TagQuotaGateMiddleware.php',
    'src/Middleware/Api/TagTenantContextMiddleware.php' => 'src/Handler/Http/TagTenantContextMiddleware.php',
    'src/Middleware/Api/TagVerifySignatureMiddleware.php' => 'src/Handler/Http/TagVerifySignatureMiddleware.php',
    'src/Ops/Metrics/TagMetrics.php' => 'src/Recorder/Metrics/TagMetrics.php',
    'src/Ops/Metrics/TagPrometheusExporter.php' => 'src/Reporter/Metrics/TagPrometheusExporter.php',
    'src/Ops/Security/TagNonceStore.php' => 'src/Cache/Security/TagNonceStore.php',
    'src/Service/Core/Slug/TagSlugPolicy.php' => 'src/Policy/Slug/TagSlugPolicy.php',
    'src/Service/Core/TagErrorSinkFactory.php' => 'src/Factory/Error/TagErrorSinkFactory.php',
    'src/Service/Security/TagHmacV2Verifier.php' => 'src/Verifier/Security/TagHmacV2Verifier.php',
    'src/Service/Core/TagEntityPayloadNormalizer.php' => 'src/Normalizer/Core/TagEntityPayloadNormalizer.php',
    'src/Service/Core/TagNormalizer.php' => 'src/Normalizer/Core/TagNormalizer.php',
    'src/Service/Http/Tag/TagAbstractService.php' => 'src/Service/Http/Tag/TagAbstractService.php',
    'src/Service/Core/TagPolicyManagementService.php' => 'src/Service/Core/TagPolicyManagementService.php',
    'config/component/tag_component.yaml' => 'config/component/tag_component.yaml',
    'config/component/tag_config_tools.yaml' => 'config/component/tag_config_tools.yaml',
    'config/component/tag_env.yaml' => 'config/component/tag_env.yaml',
    'config/component/tag_smoke.yaml' => 'config/component/tag_smoke.yaml',
    'config/tag_security_signature.yaml' => 'config/tag_security_signature.yaml',
    'config/services/tag_application.yaml' => 'config/services/tag_application.yaml',
    'config/services/tag_core.yaml' => 'config/services/tag_core.yaml',
    'config/services/tag_http.yaml' => 'config/services/tag_http.yaml',
    'config/services/tag_infrastructure.yaml' => 'config/services/tag_infrastructure.yaml',
    'config/services/tag_ops.yaml' => 'config/services/tag_ops.yaml',
    'config/services/tag_read_model.yaml' => 'config/services/tag_read_model.yaml',
    'config/services/tag_services.yaml' => 'config/services/tag_services.yaml',
];

foreach ($moves as $from => $to) {
    $source = $root . '/' . $from;
    $target = $root . '/' . $to;
    if (!is_file($source)) {
        continue;
    }
    if (is_file($target)) {
        throw new RuntimeException('Refusing to overwrite ' . $to);
    }
    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0777, true);
    }
    if (!rename($source, $target)) {
        throw new RuntimeException('Cannot move ' . $from . ' to ' . $to);
    }
}

$replacements = [
    'App\\Tagging\\EntityInterface\\Contract' => 'App\\Tagging\\Contract\\Entity',
    'App\\Tagging\\Middleware\\Api' => 'App\\Tagging\\Handler\\Http',
    'App\\Tagging\\Ops\\Metrics\\TagMetrics' => 'App\\Tagging\\Recorder\\Metrics\\TagMetrics',
    'App\\Tagging\\Ops\\Metrics\\TagPrometheusExporter' => 'App\\Tagging\\Reporter\\Metrics\\TagPrometheusExporter',
    'App\\Tagging\\Ops\\Security\\TagNonceStore' => 'App\\Tagging\\Cache\\Security\\TagNonceStore',
    'App\\Tagging\\Service\\Core\\Slug\\TagSlugPolicy' => 'App\\Tagging\\Policy\\Slug\\TagSlugPolicy',
    'App\\Tagging\\Service\\Core\\TagErrorSinkFactory' => 'App\\Tagging\\Factory\\Error\\TagErrorSinkFactory',
    'App\\Tagging\\Service\\Security\\TagHmacV2Verifier' => 'App\\Tagging\\Verifier\\Security\\TagHmacV2Verifier',
    'App\\Tagging\\Service\\Core\\TagEntityPayloadNormalizer' => 'App\\Tagging\\Normalizer\\Core\\TagEntityPayloadNormalizer',
    'App\\Tagging\\Service\\Core\\TagNormalizer' => 'App\\Tagging\\Normalizer\\Core\\TagNormalizer',
    'TagAbstractService' => 'TagAbstractService',
    'TagPolicyManagementService' => 'TagPolicyManagementService',
    'config/component/tag_component.yaml' => 'config/component/tag_component.yaml',
    'config/component/tag_config_tools.yaml' => 'config/component/tag_config_tools.yaml',
    'config/component/tag_env.yaml' => 'config/component/tag_env.yaml',
    'config/component/tag_smoke.yaml' => 'config/component/tag_smoke.yaml',
    'config/tag_security_signature.yaml' => 'config/tag_security_signature.yaml',
    'services/tag_application.yaml' => 'services/tag_application.yaml',
    'services/tag_core.yaml' => 'services/tag_core.yaml',
    'services/tag_http.yaml' => 'services/tag_http.yaml',
    'services/tag_infrastructure.yaml' => 'services/tag_infrastructure.yaml',
    'services/tag_ops.yaml' => 'services/tag_ops.yaml',
    'services/tag_read_model.yaml' => 'services/tag_read_model.yaml',
    'services/tag_services.yaml' => 'services/tag_services.yaml',
    'config/services/tag_application.yaml' => 'config/services/tag_application.yaml',
    'config/services/tag_core.yaml' => 'config/services/tag_core.yaml',
    'config/services/tag_http.yaml' => 'config/services/tag_http.yaml',
    'config/services/tag_infrastructure.yaml' => 'config/services/tag_infrastructure.yaml',
    'config/services/tag_ops.yaml' => 'config/services/tag_ops.yaml',
    'config/services/tag_read_model.yaml' => 'config/services/tag_read_model.yaml',
    'config/services/tag_services.yaml' => 'config/services/tag_services.yaml',
];

$scanRoots = ['src', 'config', 'tests', 'tools', 'docs', 'delivery'];
$extensions = ['php', 'yaml', 'yml', 'xml', 'neon', 'md', 'adoc', 'json'];
foreach ($scanRoots as $scanRoot) {
    $directory = $root . '/' . $scanRoot;
    if (!is_dir($directory)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $extensions, true)) {
            continue;
        }
        $contents = file_get_contents($file->getPathname());
        if (false === $contents) {
            throw new RuntimeException('Cannot read ' . $file->getPathname());
        }
        $updated = strtr($contents, $replacements);
        if ($updated !== $contents && false === file_put_contents($file->getPathname(), $updated)) {
            throw new RuntimeException('Cannot write ' . $file->getPathname());
        }
    }
}

$namespaceFixes = [
    'src/Contract/Entity/TagInterface.php' => ['namespace App\\Tagging\\EntityInterface\\Contract;' => 'namespace App\\Tagging\\Contract\\Entity;'],
    'src/Contract/Entity/TagProductInterface.php' => ['namespace App\\Tagging\\EntityInterface\\Contract;' => 'namespace App\\Tagging\\Contract\\Entity;'],
    'src/Contract/Entity/TagProjectInterface.php' => ['namespace App\\Tagging\\EntityInterface\\Contract;' => 'namespace App\\Tagging\\Contract\\Entity;'],
    'src/Handler/Http/TagAuthorizeMiddleware.php' => ['namespace App\\Tagging\\Middleware\\Api;' => 'namespace App\\Tagging\\Handler\\Http;'],
    'src/Handler/Http/TagIdempotencyMiddleware.php' => ['namespace App\\Tagging\\Http\\Middleware;' => 'namespace App\\Tagging\\Handler\\Http;'],
    'src/Handler/Http/TagMiddlewarePipeline.php' => ['namespace App\\Tagging\\Middleware\\Api;' => 'namespace App\\Tagging\\Handler\\Http;'],
    'src/Handler/Http/TagObserveMiddleware.php' => ['namespace App\\Tagging\\Middleware\\Api;' => 'namespace App\\Tagging\\Handler\\Http;'],
    'src/Handler/Http/TagQuotaGateMiddleware.php' => ['namespace App\\Tagging\\Middleware\\Api;' => 'namespace App\\Tagging\\Handler\\Http;'],
    'src/Handler/Http/TagTenantContextMiddleware.php' => ['namespace App\\Tagging\\Middleware\\Api;' => 'namespace App\\Tagging\\Handler\\Http;'],
    'src/Handler/Http/TagVerifySignatureMiddleware.php' => ['namespace App\\Tagging\\Middleware\\Api;' => 'namespace App\\Tagging\\Handler\\Http;'],
    'src/Recorder/Metrics/TagMetrics.php' => ['namespace App\\Tagging\\Ops\\Metrics;' => 'namespace App\\Tagging\\Recorder\\Metrics;'],
    'src/Reporter/Metrics/TagPrometheusExporter.php' => ['namespace App\\Tagging\\Ops\\Metrics;' => 'namespace App\\Tagging\\Reporter\\Metrics;'],
    'src/Cache/Security/TagNonceStore.php' => ['namespace App\\Tagging\\Ops\\Security;' => 'namespace App\\Tagging\\Cache\\Security;'],
    'src/Policy/Slug/TagSlugPolicy.php' => ['namespace App\\Tagging\\Service\\Core\\Slug;' => 'namespace App\\Tagging\\Policy\\Slug;'],
    'src/Factory/Error/TagErrorSinkFactory.php' => ['namespace App\\Tagging\\Service\\Core;' => 'namespace App\\Tagging\\Factory\\Error;'],
    'src/Verifier/Security/TagHmacV2Verifier.php' => ['namespace App\\Tagging\\Service\\Security;' => 'namespace App\\Tagging\\Verifier\\Security;'],
    'src/Normalizer/Core/TagEntityPayloadNormalizer.php' => ['namespace App\\Tagging\\Service\\Core;' => 'namespace App\\Tagging\\Normalizer\\Core;'],
    'src/Normalizer/Core/TagNormalizer.php' => ['namespace App\\Tagging\\Service\\Core;' => 'namespace App\\Tagging\\Normalizer\\Core;'],
];

foreach ($namespaceFixes as $relative => $pairs) {
    $path = $root . '/' . $relative;
    $contents = file_get_contents($path);
    if (false === $contents) {
        throw new RuntimeException('Cannot read ' . $relative);
    }
    $updated = strtr($contents, $pairs);
    if ($updated !== $contents) {
        file_put_contents($path, $updated);
    }
}

$factory = $root . '/src/Factory/Config/TagRuntimeConfigFactory.php';
if (is_file($factory)) {
    $contents = (string) file_get_contents($factory);
    $contents = str_replace('TagRuntimeConfigDTOFactory', 'TagRuntimeConfigFactory', $contents);
    file_put_contents($factory, $contents);
}

$artifactRoot = $root . '/.gating';
$allowed = ['README.md', 'report', 'reports', 'evidence', 'cache', 'checksum', 'checksums', 'artifact', 'artifacts'];
$removeTree = static function (string $path) use (&$removeTree): void {
    if (is_dir($path) && !is_link($path)) {
        foreach (scandir($path) ?: [] as $entry) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }
            $removeTree($path . DIRECTORY_SEPARATOR . $entry);
        }
        rmdir($path);
        return;
    }
    if (file_exists($path) || is_link($path)) {
        unlink($path);
    }
};
if (is_dir($artifactRoot)) {
    foreach (scandir($artifactRoot) ?: [] as $entry) {
        if ('.' === $entry || '..' === $entry || in_array($entry, $allowed, true)) {
            continue;
        }
        $removeTree($artifactRoot . DIRECTORY_SEPARATOR . $entry);
    }
}

$removeEmpty = static function (string $directory) use (&$removeEmpty): void {
    if (!is_dir($directory)) {
        return;
    }
    foreach (scandir($directory) ?: [] as $entry) {
        if ('.' === $entry || '..' === $entry) {
            continue;
        }
        $path = $directory . DIRECTORY_SEPARATOR . $entry;
        if (is_dir($path)) {
            $removeEmpty($path);
        }
    }
    $entries = array_values(array_diff(scandir($directory) ?: [], ['.', '..']));
    if ([] === $entries) {
        rmdir($directory);
    }
};
foreach (['src/Application', 'src/Infrastructure', 'src/Http', 'src/EntityInterface', 'src/Middleware', 'src/Ops'] as $legacyRoot) {
    $removeEmpty($root . '/' . $legacyRoot);
}

fwrite(STDOUT, "Tagging RC structural cleanup applied.\n");
