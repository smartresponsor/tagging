<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$loadFixture = require __DIR__ . '/tag-demo-fixture-loader.php';
$data = $loadFixture($root);
$errors = [];
$ids = [];
$slugs = [];

foreach (($data['tags'] ?? []) as $tag) {
    $id = trim((string) ($tag['id'] ?? ''));
    $slug = trim((string) ($tag['slug'] ?? ''));

    if ('' === $id) {
        $errors[] = 'empty tag id';
        continue;
    }
    if ('' === $slug) {
        $errors[] = 'empty slug for tag ' . $id;
        continue;
    }
    if (isset($ids[$id])) {
        $errors[] = 'duplicate tag id ' . $id;
    }
    if (isset($slugs[$slug])) {
        $errors[] = 'duplicate slug ' . $slug;
    }

    $ids[$id] = true;
    $slugs[$slug] = true;
}

$links = [];
foreach (($data['links'] ?? []) as $link) {
    $entityType = trim((string) ($link['entity_type'] ?? ''));
    $entityId = trim((string) ($link['entity_id'] ?? ''));
    $tagId = trim((string) ($link['tag_id'] ?? ''));

    if ('' === $entityType || '' === $entityId || '' === $tagId) {
        $errors[] = 'invalid link row';
        continue;
    }
    if (!isset($ids[$tagId])) {
        $errors[] = 'link references unknown tag_id ' . $tagId;
    }

    $key = $entityType . '|' . $entityId . '|' . $tagId;
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
