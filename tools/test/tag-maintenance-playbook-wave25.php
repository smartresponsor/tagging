<?php

declare(strict_types=1);

/**
 * Verifies that the Tagging post-canonicalization maintenance playbook exists
 * and preserves the component namespace policy.
 */
$repoRoot = dirname(__DIR__, 2);

$playbookPath = $repoRoot . '/docs/architecture/tagging-wave25-maintenance-playbook.md';
if (!is_file($playbookPath)) {
    fwrite(STDERR, "Missing Tagging maintenance playbook.\n");

    exit(1);
}

$playbook = (string) file_get_contents($playbookPath);
$requiredMarkers = [
    'App\\Tagging\\...',
    'touched-files only',
    'No repository-wide cleanup',
    'tag-post-canon-health-wave24.php',
    'tag-post-canon-all-wave22.php',
];

foreach ($requiredMarkers as $marker) {
    if (!str_contains($playbook, $marker)) {
        fwrite(STDERR, sprintf("Maintenance playbook is missing marker: %s\n", $marker));

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

echo "Tagging maintenance playbook audit passed.\n";
