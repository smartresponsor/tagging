<?php

declare(strict_types=1);

/**
 * Ensures the Tagging delivery RC manifest has a component-scoped filename.
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
    'delivery/rc/manifest.yaml',
];

$required = [
    'delivery/rc/tag-rc-manifest.yaml',
];

$violations = [];
foreach ($forbidden as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'forbidden generic delivery manifest still exists: ' . $relativePath;
    }
}

foreach ($required as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required canonical delivery manifest missing: ' . $relativePath;
    }
}

$scanRoots = ['docs', 'tests', 'tools', 'delivery'];
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
        if ($relativePath === 'tools/audit/tag-delivery-manifest-wave16-audit.php') {
            continue;
        }

        $contents = (string) file_get_contents($fileInfo->getPathname());
        if (str_contains($contents, 'delivery/rc/manifest.yaml')) {
            $violations[] = 'legacy delivery manifest reference in: ' . $relativePath;
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging delivery manifest audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging delivery manifest audit passed.\n";
