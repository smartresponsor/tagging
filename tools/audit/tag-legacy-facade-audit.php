<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$forbidden = [
    'src/Http/Api/Tag/MetricsController.php',
    'src/Service/Authz/TagAuthorizer.php',
    'src/Service/Slug/Tag/TagSlugPolicy.php',
    'src/Service/Slug/Tag/TagSlugifier.php',
];

$violations = [];
foreach ($forbidden as $relative) {
    if (is_file($root . '/' . $relative)) {
        $violations[] = 'Forbidden legacy facade/surface remains: ' . $relative;
    }
}

$scanFiles = [];
foreach (['src', 'config', 'tests', 'docs', 'release', 'tools'] as $dir) {
    $path = $root . '/' . $dir;
    if (!is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $name = $file->getFilename();
        if (!preg_match('/\.(php|yaml|yml|json|md|adoc|txt)$/', $name)) {
            continue;
        }
        $scanFiles[] = $file->getPathname();
    }
}

$forbiddenReferences = [
    'App\\Tagging\\Http\\Api\\Tag\\MetricsController',
    'App\\Tagging\\Service\\Authz\\TagAuthorizer',
    'App\\Tagging\\Service\\Slug\\Tag\\TagSlugPolicy',
    'App\\Tagging\\Service\\Slug\\Tag\\TagSlugifier',
    'src/Http/Api/Tag/MetricsController.php',
    'src/Service/Authz/TagAuthorizer.php',
    'src/Service/Slug/Tag/TagSlugPolicy.php',
    'src/Service/Slug/Tag/TagSlugifier.php',
];

foreach ($scanFiles as $file) {
    $relative = str_replace($root . '/', '', $file);
    if (in_array($relative, [
        'tools/audit/tag-legacy-facade-audit.php',
        'tools/audit/tag-legacy-duplicate-surface-audit.php',
        'tools/audit/tag-duplicate-residue-audit.php',
        'docs/architecture/tagging-wave4-legacy-facade-retirement.md',
        'docs/architecture/tagging-wave4-legacy-facade-cleanup.md',
        'docs/architecture/tagging-wave5-legacy-duplicate-surface-cleanup.md',
        'docs/architecture/tagging-wave9-duplicate-residue-cleanup.md',
    ], true)) {
        continue;
    }
    $contents = file_get_contents($file);
    if (false === $contents) {
        continue;
    }
    foreach ($forbiddenReferences as $needle) {
        if (str_contains($contents, $needle)) {
            $violations[] = sprintf('Forbidden legacy reference `%s` in %s', $needle, $relative);
        }
    }
}

if ([] !== $violations) {
    fwrite(STDERR, implode(PHP_EOL, $violations) . PHP_EOL);
    exit(1);
}

echo 'Tag legacy facade audit passed.' . PHP_EOL;
