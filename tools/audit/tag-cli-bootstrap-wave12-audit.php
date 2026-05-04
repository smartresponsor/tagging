<?php

declare(strict_types=1);

/**
 * Ensures Tagging CLI/bootstrap tooling entrypoints are component-scoped and
 * no generic `_bootstrap.php` or ambiguous `tools/cli/tag.php` entrypoint remains.
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
    'tools/_bootstrap.php',
    'tools/cli/tag.php',
];

$required = [
    'tools/tag-bootstrap.php',
    'tools/cli/tag-cli.php',
];

$violations = [];
foreach ($forbidden as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'forbidden generic tooling entrypoint still exists: ' . $relativePath;
    }
}

foreach ($required as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required canonical tooling entrypoint missing: ' . $relativePath;
    }
}

$scanRoots = [
    'docs',
    'tests',
    'tools',
];

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
        if ($relativePath === 'tools/audit/tag-cli-bootstrap-wave12-audit.php') {
            continue;
        }

        $contents = (string) file_get_contents($fileInfo->getPathname());
        if (str_contains($contents, 'tools/_bootstrap.php') || str_contains($contents, '../_bootstrap.php')) {
            $violations[] = 'legacy bootstrap reference in: ' . $relativePath;
        }

        if (str_contains($contents, 'tools/cli/tag.php')) {
            $violations[] = 'legacy CLI reference in: ' . $relativePath;
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging CLI/bootstrap audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging CLI/bootstrap audit passed.\n";
