<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$requiredFiles = [
    $root . '/CHANGELOG.md',
    $root . '/RELEASE_NOTES.md',
    $root . '/docs/public/index.md',
    $root . '/docs/release/rc-checklist.md',
    $root . '/docs/ops/runbook.md',
    $root . '/.github/workflows/release-rc.yml',
];

foreach ($requiredFiles as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, 'Missing release asset: ' . str_replace($root . '/', '', $file) . PHP_EOL);
        exit(1);
    }
}

$changelog = file_get_contents($root . '/CHANGELOG.md') ?: '';
$notes = file_get_contents($root . '/RELEASE_NOTES.md') ?: '';
$publicIndex = file_get_contents($root . '/docs/public/index.md') ?: '';
$checklist = file_get_contents($root . '/docs/release/rc-checklist.md') ?: '';
$workflow = file_get_contents($root . '/.github/workflows/release-rc.yml') ?: '';

$checks = [
    ['CHANGELOG.md', $changelog, '0.2.8-rc1'],
    ['CHANGELOG.md', $changelog, 'release asset lane'],
    ['RELEASE_NOTES.md', $notes, 'prerelease / RC candidate'],
    ['RELEASE_NOTES.md', $notes, 'composer run -n release:preflight'],
    ['RELEASE_NOTES.md', $notes, 'composer run -n audit:openapi-semantics'],
    ['docs/public/index.md', $publicIndex, 'docs/ops/runbook.md'],
    ['docs/public/index.md', $publicIndex, 'contracts/http/tag-openapi.yaml'],
    ['docs/release/rc-checklist.md', $checklist, 'v0.2.8-rc1'],
    ['docs/release/rc-checklist.md', $checklist, 'composer run -n audit:release-assets'],
    ['.github/workflows/release-rc.yml', $workflow, 'composer run -n release:preflight'],
    ['.github/workflows/release-rc.yml', $workflow, 'composer run -n audit:release-assets'],
    ['.github/workflows/release-rc.yml', $workflow, 'composer run -n audit:openapi-semantics'],
    ['.github/workflows/release-rc.yml', $workflow, 'uses: actions/upload-artifact@v4'],
];

foreach ($checks as [$label, $haystack, $needle]) {
    if (!str_contains($haystack, $needle)) {
        fwrite(STDERR, sprintf('Release asset mismatch in %s: missing %s', $label, $needle) . PHP_EOL);
        exit(1);
    }
}

echo "tag-release-assets-audit: ok\n";
