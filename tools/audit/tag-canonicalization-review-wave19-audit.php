<?php

declare(strict_types=1);

/**
 * Ensures the Tagging canonicalization review report is present and aligned with
 * the current milestone namespace policy.
 */
$repoRoot = dirname(__DIR__, 2);

$reportPath = $repoRoot . '/docs/architecture/tagging-wave19-canonicalization-review.md';
if (!is_file($reportPath)) {
    fwrite(STDERR, "Missing Wave 19 canonicalization review report.\n");

    exit(1);
}

$report = (string) file_get_contents($reportPath);
$requiredNeedles = [
    'App\\Tagging\\...',
    'tools/audit/tag-canon-milestone-wave18-audit.php',
    'Remaining intentionally generic artifacts',
    'This wave intentionally avoids broad cleanup.',
];

foreach ($requiredNeedles as $needle) {
    if (!str_contains($report, $needle)) {
        fwrite(STDERR, sprintf("Wave 19 review report is missing required marker: %s\n", $needle));

        exit(1);
    }
}

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

echo "Tagging Wave 19 review report audit passed.\n";
