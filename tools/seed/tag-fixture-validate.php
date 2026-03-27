<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$data = json_decode((string) file_get_contents($root . '/fixtures/tag-demo.json'), true);
if (!is_array($data)) {
    fwrite(STDERR, "invalid fixture json\n");
    exit(1);
}
$errors = [];
$slugs = [];
foreach (($data['tags'] ?? []) as $tag) {
    $slug = (string) ($tag['slug'] ?? '');
    if ($slug === '') {
        $errors[] = 'empty slug';
        continue;
    }
    if (isset($slugs[$slug])) {
        $errors[] = 'duplicate slug ' . $slug;
    }
    $slugs[$slug] = true;
}
$links = [];
foreach (($data['links'] ?? []) as $link) {
    $slug = (string) ($link['slug'] ?? '');
    if (!isset($slugs[$slug])) {
        $errors[] = 'link references unknown slug ' . $slug;
    }
    $key = (string) ($link['entity_type'] ?? '') . '|' . (string) ($link['entity_id'] ?? '') . '|' . $slug;
    if (isset($links[$key])) {
        $errors[] = 'duplicate link ' . $key;
    }
    $links[$key] = true;
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "tag-fixture-validate: ok\n";
