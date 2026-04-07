<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = $root . '/contracts/http/tag-openapi.yaml';
$generated = $root . '/public/tag/openapi/tag-openapi.yaml';
$index = $root . '/public/tag/openapi/index.html';

foreach ([$source, $generated, $index] as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, 'Missing generated OpenAPI surface file: ' . str_replace($root . '/', '', $file) . PHP_EOL);
        exit(1);
    }
}

$sourceText = file_get_contents($source) ?: '';
$generatedText = file_get_contents($generated) ?: '';
$indexText = file_get_contents($index) ?: '';

if ($sourceText !== $generatedText) {
    fwrite(STDERR, "Generated OpenAPI artifact is out of sync with source contract.\n");
    exit(1);
}

$needles = [
    './tag-openapi.yaml',
    'swagger-ui-bundle.js',
    'SwaggerUIBundle',
];

foreach ($needles as $needle) {
    if (!str_contains($indexText, $needle)) {
        fwrite(STDERR, 'Generated OpenAPI index is missing: ' . $needle . PHP_EOL);
        exit(1);
    }
}

echo "tag-generated-openapi-surface-audit: ok\n";
