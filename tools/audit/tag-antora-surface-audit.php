<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$requiredFiles = [
    $root . '/docs/antora.yml',
    $root . '/docs/modules/ROOT/nav.adoc',
    $root . '/docs/modules/ROOT/pages/index.adoc',
    $root . '/docs/modules/ROOT/pages/architecture.adoc',
    $root . '/docs/modules/ROOT/pages/install.adoc',
    $root . '/docs/modules/ROOT/pages/operations.adoc',
    $root . '/docs/modules/ROOT/pages/api.adoc',
];

foreach ($requiredFiles as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, 'Missing Antora surface file: ' . str_replace($root . '/', '', $file) . PHP_EOL);
        exit(1);
    }
}

$antora = file_get_contents($root . '/docs/antora.yml') ?: '';
$nav = file_get_contents($root . '/docs/modules/ROOT/nav.adoc') ?: '';
$index = file_get_contents($root . '/docs/modules/ROOT/pages/index.adoc') ?: '';
$architecture = file_get_contents($root . '/docs/modules/ROOT/pages/architecture.adoc') ?: '';
$install = file_get_contents($root . '/docs/modules/ROOT/pages/install.adoc') ?: '';
$operations = file_get_contents($root . '/docs/modules/ROOT/pages/operations.adoc') ?: '';
$api = file_get_contents($root . '/docs/modules/ROOT/pages/api.adoc') ?: '';

$checks = [
    ['docs/antora.yml', $antora, 'start_page: ROOT:index.adoc'],
    ['docs/antora.yml', $antora, 'modules/ROOT/nav.adoc'],
    ['docs/modules/ROOT/nav.adoc', $nav, 'xref:index.adoc[Tagging]'],
    ['docs/modules/ROOT/nav.adoc', $nav, 'xref:architecture.adoc[Architecture]'],
    ['docs/modules/ROOT/pages/index.adoc', $index, 'GitHub-facing repository docs'],
    ['docs/modules/ROOT/pages/index.adoc', $index, 'contracts/http/tag-openapi.yaml'],
    ['docs/modules/ROOT/pages/index.adoc', $index, 'No Nelmio runtime surface is shipped here.'],
    ['docs/modules/ROOT/pages/index.adoc', $index, 'docs/README.md'],
    ['docs/modules/ROOT/pages/architecture.adoc', $architecture, 'docs/http/http-wiring.md'],
    ['docs/modules/ROOT/pages/install.adoc', $install, 'docs/ops/runbook.md'],
    ['docs/modules/ROOT/pages/operations.adoc', $operations, 'docs/ops/observability.md'],
    ['docs/modules/ROOT/pages/api.adoc', $api, 'contracts/http/tag-openapi.yaml'],
    ['docs/modules/ROOT/pages/api.adoc', $api, 'public/tag/openapi/'],
    ['docs/modules/ROOT/pages/api.adoc', $api, 'generated static Swagger/OpenAPI surface is shipped separately from narrative docs'],
    ['docs/modules/ROOT/pages/api.adoc', $api, 'release/tag-rc5/docs/'],
];

foreach ($checks as [$label, $haystack, $needle]) {
    if (!str_contains($haystack, $needle)) {
        fwrite(STDERR, sprintf('Antora surface mismatch in %s: missing %s', $label, $needle) . PHP_EOL);
        exit(1);
    }
}

echo "tag-antora-surface-audit: ok\n";
