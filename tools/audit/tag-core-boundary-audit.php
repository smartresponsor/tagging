<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$readme = $root . '/README.md';
$boundary = $root . '/docs/architecture/tag-core-boundary.md';
$demo = $root . '/docs/demo/tag-quick-demo.md';

if (!is_file($readme)) {
    $errors[] = 'README.md is missing';
} else {
    $content = (string) file_get_contents($readme);
    foreach ([
        '## Runnable core',
        '## Adjacent assets (not core runtime)',
        '`GET /tag/_surface`',
        '`GET /tag/_status`',
    ] as $needle) {
        if (!str_contains($content, $needle)) {
            $errors[] = 'README.md missing required section: ' . $needle;
        }
    }

    if (substr_count($content, '## Publish gate') !== 1) {
        $errors[] = 'README.md must contain exactly one publish gate section';
    }
}

if (!is_file($boundary)) {
    $errors[] = 'docs/architecture/tag-core-boundary.md is missing';
} else {
    $content = (string) file_get_contents($boundary);
    foreach (['## Core runtime', '## Adjacent assets', '## Truth order'] as $needle) {
        if (!str_contains($content, $needle)) {
            $errors[] = 'tag-core-boundary.md missing required section: ' . $needle;
        }
    }
}

if (!is_file($demo)) {
    $errors[] = 'docs/demo/tag-quick-demo.md is missing';
} else {
    $content = (string) file_get_contents($demo);
    if (substr_count($content, '## 2. Create') !== 1) {
        $errors[] = 'tag-quick-demo.md must contain exactly one create step';
    }
    foreach (['/tag/_surface', '/tag/_status', '/tag/search', '/tag/suggest'] as $needle) {
        if (!str_contains($content, $needle)) {
            $errors[] = 'tag-quick-demo.md missing endpoint: ' . $needle;
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Core boundary audit failed:
- " . implode("
- ", $errors) . "
");
    exit(1);
}

echo "Core boundary audit passed
";
