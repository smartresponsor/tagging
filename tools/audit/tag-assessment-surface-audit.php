<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$requiredFiles = [
    $root . '/.github/workflows/quality-atlas.yml',
    $root . '/docs/ops/quality-atlas.md',
    $root . '/docs/public/index.md',
    $root . '/docs/release/rc-checklist.md',
    $root . '/contracts/http/tag-openapi.yaml',
    $root . '/public/tag/openapi/index.html',
];

foreach ($requiredFiles as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, 'Missing assessment surface asset: ' . str_replace($root . '/', '', $file) . PHP_EOL);
        exit(1);
    }
}

$workflow = (string) file_get_contents($root . '/.github/workflows/quality-atlas.yml');
$doc = (string) file_get_contents($root . '/docs/ops/quality-atlas.md');
$publicIndex = (string) file_get_contents($root . '/docs/public/index.md');
$checklist = (string) file_get_contents($root . '/docs/release/rc-checklist.md');

$checks = [
    ['.github/workflows/quality-atlas.yml', $workflow, 'workflow_dispatch'],
    ['.github/workflows/quality-atlas.yml', $workflow, 'schedule:'],
    ['.github/workflows/quality-atlas.yml', $workflow, 'composer run -n audit:assessment-surface'],
    ['.github/workflows/quality-atlas.yml', $workflow, 'composer run -n phpstan'],
    ['.github/workflows/quality-atlas.yml', $workflow, 'uses: actions/upload-artifact@v4'],
    ['docs/ops/quality-atlas.md', $doc, '.github/workflows/quality-atlas.yml'],
    ['docs/ops/quality-atlas.md', $doc, 'tools/audit/tag-assessment-surface-audit.php'],
    ['docs/ops/quality-atlas.md', $doc, 'producer-only'],
    ['docs/public/index.md', $publicIndex, 'docs/ops/quality-atlas.md'],
    ['docs/release/rc-checklist.md', $checklist, 'quality-atlas assessment workflow reviewed'],
];

foreach ($checks as [$label, $haystack, $needle]) {
    if (!str_contains($haystack, $needle)) {
        fwrite(STDERR, sprintf('Assessment surface mismatch in %s: missing %s', $label, $needle) . PHP_EOL);
        exit(1);
    }
}

echo "tag-assessment-surface-audit: ok\n";
