<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$targets = [
    $root . '/README.md',
    $root . '/repo-map.md',
    $root . '/docs/architecture/repository-production-readiness-plan.md',
    $root . '/docs/http/http-wiring.md',
    $root . '/docs/deploy/symfony-native-search.md',
];

$forbidden = [
    'src/Domain/',
    'src/Infra/',
    'App\\Domain\\',
    'App\\Infra\\',
    'src/Application/Tag/',
    'src/Cache/Tag/',
    'src/Data/Tag/',
    'src/Http/Tag/',
    'src/Service/Tag/',
    'src/ServiceInterface/',
];

$hits = [];
foreach ($targets as $file) {
    if (!is_file($file)) {
        continue;
    }
    $content = (string) file_get_contents($file);
    foreach ($forbidden as $needle) {
        if (str_contains($content, $needle)) {
            $hits[] = $file . ' => ' . $needle;
        }
    }
}

if ($hits !== []) {
    fwrite(STDERR, 'Stale canonical references found:' . PHP_EOL . implode(PHP_EOL, $hits) . PHP_EOL);
    exit(1);
}

echo 'tag canonical stale reference audit: ok' . PHP_EOL;
