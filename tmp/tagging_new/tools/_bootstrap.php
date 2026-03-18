<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

require_once dirname(__DIR__) . '/host-minimal/autoload.php';

function tag_root(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $path === '' ? $root : $root . '/' . ltrim($path, '/');
}

/** @return array<string,mixed> */
function tag_surface_catalog(): array
{
    /** @var array<string,mixed> $cfg */
    $cfg = require tag_root('config/tag_public_surface.php');
    return $cfg;
}

/** @return array<string,mixed> */
function tag_manifest(): array
{
    $json = file_get_contents(tag_root('MANIFEST.json')) ?: '{}';
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function tag_assert(bool $ok, string $message): void
{
    if (!$ok) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}
