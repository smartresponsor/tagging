<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$requiredFiles = [
    $root . '/docs/demo/tag-final-demo-pack.md',
    $root . '/docs/demo/tag-quick-demo.md',
    $root . '/docs/fixtures/demo.md',
    $root . '/docs/seed/tag-seed.md',
    $root . '/fixtures/tag-demo-fixture.php',
    $root . '/fixtures/tag-demo-catalog.php',
    $root . '/seed/tag/tag-demo.ndjson',
    $root . '/seed/tag/tag-links-demo.ndjson',
];

foreach ($requiredFiles as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, 'Missing demo truth-pack file: ' . str_replace($root . '/', '', $file) . PHP_EOL);
        exit(1);
    }
}

$finalPack = file_get_contents($root . '/docs/demo/tag-final-demo-pack.md') ?: '';
$quickDemo = file_get_contents($root . '/docs/demo/tag-quick-demo.md') ?: '';
$fixtureDemo = file_get_contents($root . '/docs/fixtures/demo.md') ?: '';
$seedDoc = file_get_contents($root . '/docs/seed/tag-seed.md') ?: '';

$checks = [
    ['docs/demo/tag-final-demo-pack.md', $finalPack, '`host-minimal/`'],
    ['docs/demo/tag-final-demo-pack.md', $finalPack, '`fixtures/tag-demo-fixture.php`'],
    ['docs/demo/tag-final-demo-pack.md', $finalPack, '`seed/tag/tag-demo.ndjson`'],
    ['docs/demo/tag-final-demo-pack.md', $finalPack, '/tag/_surface'],
    ['docs/demo/tag-final-demo-pack.md', $finalPack, '/tag/_status'],
    ['docs/demo/tag-quick-demo.md', $quickDemo, '/tag/_surface'],
    ['docs/demo/tag-quick-demo.md', $quickDemo, '/tag/_status'],
    ['docs/demo/tag-quick-demo.md', $quickDemo, 'tools/seed/tag-seed.php'],
    ['docs/fixtures/demo.md', $fixtureDemo, 'fixtures/tag-demo-catalog.php'],
    ['docs/fixtures/demo.md', $fixtureDemo, 'seed/tag/tag-links-demo.ndjson'],
    ['docs/seed/tag-seed.md', $seedDoc, 'tools/db/tag-migrate.php'],
    ['docs/seed/tag-seed.md', $seedDoc, '/tag/_surface'],
    ['docs/seed/tag-seed.md', $seedDoc, '/tag/_status'],
];

foreach ($checks as [$label, $haystack, $needle]) {
    if (!str_contains($haystack, $needle)) {
        fwrite(STDERR, sprintf('Demo truth-pack mismatch in %s: missing %s', $label, $needle) . PHP_EOL);
        exit(1);
    }
}

echo "OK\n";
