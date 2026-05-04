<?php

declare(strict_types=1);

/**
 * Ensures public Tagging demo/example HTTP artifacts use component-scoped names.
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
    'public/tag/demo/requests.http',
    'public/tag/examples/http.http',
    'public/tag/examples/seed.http',
    'public/tag/examples/tour.http',
];

$required = [
    'public/tag/demo/tag-demo-requests.http',
    'public/tag/examples/tag-http-examples.http',
    'public/tag/examples/tag-seed-examples.http',
    'public/tag/examples/tag-tour-examples.http',
];

$violations = [];
foreach ($forbidden as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'forbidden generic public HTTP example still exists: ' . $relativePath;
    }
}

foreach ($required as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required canonical public HTTP example missing: ' . $relativePath;
    }
}

$scanRoots = ['docs', 'tests', 'tools', 'public'];
foreach ($scanRoots as $scanRoot) {
    $absoluteRoot = $repoRoot . '/' . $scanRoot;
    if (!is_dir($absoluteRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absoluteRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
            continue;
        }

        $relativePath = substr($fileInfo->getPathname(), strlen($repoRoot) + 1);
        if ($relativePath === 'tools/audit/tag-public-example-wave14-audit.php') {
            continue;
        }

        $contents = (string) file_get_contents($fileInfo->getPathname());
        foreach ($forbidden as $legacyReference) {
            if (str_contains($contents, $legacyReference)) {
                $violations[] = 'legacy public HTTP example reference in ' . $relativePath . ': ' . $legacyReference;
            }
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging public example audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging public example audit passed.\n";
