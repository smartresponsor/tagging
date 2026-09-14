<?php

declare(strict_types=1);

/**
 * Stabilizes prior Tagging cleanup audits after canonical namespace and tooling waves.
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

$bundlePath = $repoRoot . '/src/TaggingBundle.php';
if (is_file($bundlePath)) {
    $bundle = (string) file_get_contents($bundlePath);
    if (!str_contains($bundle, 'namespace App\\Tagging;')) {
        fwrite(STDERR, "TaggingBundle.php must keep root component namespace App\\Tagging.\n");

        exit(1);
    }
}

$forbiddenLiteralResidue = [
    'tag-tag-smoke',
    'tools/smoke/tag-tag-smoke.sh',
];

$scanFiles = [
    'tools/audit/tag-tooling-entrypoint-audit.php',
    'tools/audit/tag-tooling-surface-wave10-audit.php',
    'docs/architecture/tagging-wave10-tooling-surface-cleanup.md',
    'composer.json',
];

foreach ($scanFiles as $relativePath) {
    $absolutePath = $repoRoot . '/' . $relativePath;
    if (!is_file($absolutePath)) {
        continue;
    }

    $contents = (string) file_get_contents($absolutePath);
    foreach ($forbiddenLiteralResidue as $needle) {
        if (str_contains($contents, $needle)) {
            fwrite(STDERR, sprintf("Forbidden tooling rename residue `%s` in %s\n", $needle, $relativePath));

            exit(1);
        }
    }
}

$requiredCanonicalReferences = [
    'tools/smoke/tag-smoke.sh',
    'tools/tag-migration-smoke.sh',
    'tools/tag-test-db-start.sh',
    'tools/tag-webhook-worker.php',
];

foreach ($requiredCanonicalReferences as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        fwrite(STDERR, "Required canonical tooling file missing: {$relativePath}\n");

        exit(1);
    }
}

echo "Tagging audit stabilization passed.\n";
