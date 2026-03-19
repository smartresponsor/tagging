<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$doc = $root . '/docs/release/tag-release-grade-portrait.md';
$readme = $root . '/README.md';
$demo = $root . '/docs/demo/tag-final-demo-pack.md';

foreach ([$doc, $readme, $demo] as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, 'Missing release-grade portrait dependency: ' . str_replace($root . '/', '', $file) . PHP_EOL);
        exit(1);
    }
}

$docText = file_get_contents($doc) ?: '';
$readmeText = file_get_contents($readme) ?: '';
$demoText = file_get_contents($demo) ?: '';

$checks = [
    ['docs/release/tag-release-grade-portrait.md', $docText, '`src/`'],
    ['docs/release/tag-release-grade-portrait.md', $docText, '`host-minimal/`'],
    ['docs/release/tag-release-grade-portrait.md', $docText, 'status.db_probe_failed'],
    ['docs/release/tag-release-grade-portrait.md', $docText, 'quota.count_failed'],
    ['docs/release/tag-release-grade-portrait.md', $docText, 'assign_failed'],
    ['docs/release/tag-release-grade-portrait.md', $docText, 'unassign_failed'],
    ['docs/release/tag-release-grade-portrait.md', $docText, 'composer test'],
    ['docs/release/tag-release-grade-portrait.md', $docText, 'audit:demo-truth-pack'],
    ['docs/release/tag-release-grade-portrait.md', $docText, '/tag/_surface'],
    ['docs/release/tag-release-grade-portrait.md', $docText, '/tag/_status'],
    ['README.md', $readmeText, 'Runnable core'],
    ['README.md', $readmeText, 'Adjacent assets (not core runtime)'],
    ['docs/demo/tag-final-demo-pack.md', $demoText, '/tag/_surface'],
    ['docs/demo/tag-final-demo-pack.md', $demoText, '/tag/_status'],
];

foreach ($checks as [$label, $haystack, $needle]) {
    if (!str_contains($haystack, $needle)) {
        fwrite(STDERR, sprintf('Release-grade portrait mismatch in %s: missing %s', $label, $needle) . PHP_EOL);
        exit(1);
    }
}

echo "OK
";
