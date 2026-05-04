<?php

declare(strict_types=1);

/**
 * Ensures the static Tagging admin UI uses component-scoped asset names rather
 * than generic app/style filenames.
 */
$repoRoot = dirname(__DIR__, 2);

$composerPath = $repoRoot . '/composer.json';
if (!is_file($composerPath)) {
    fwrite(STDERR, "Missing composer.json\n");

    exit(1);
}

$composer = (string) file_get_contents($composerPath);
if (!str_contains($composer, 'App\\\\Tagging\\\\')) {
    fwrite(STDERR, "composer.json must keep App\\Tagging\\ as the component namespace.\n");

    exit(1);
}

$forbidden = [
    'admin/app.js',
    'admin/style.css',
];

$required = [
    'admin/index.html',
    'admin/tag-admin.js',
    'admin/tag-admin.css',
];

$violations = [];
foreach ($forbidden as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'forbidden generic admin asset still exists: ' . $relativePath;
    }
}

foreach ($required as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required canonical admin asset missing: ' . $relativePath;
    }
}

$indexPath = $repoRoot . '/admin/index.html';
if (is_file($indexPath)) {
    $index = (string) file_get_contents($indexPath);

    if (str_contains($index, 'app.js') || str_contains($index, 'style.css')) {
        $violations[] = 'admin/index.html still references generic admin asset names';
    }

    if (!str_contains($index, 'tag-admin.js') || !str_contains($index, 'tag-admin.css')) {
        $violations[] = 'admin/index.html must reference tag-admin.js and tag-admin.css';
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging admin asset audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging admin asset audit passed.\n";
