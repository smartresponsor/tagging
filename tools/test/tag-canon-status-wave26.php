<?php

declare(strict_types=1);

/**
 * Verifies the machine-readable Tagging canon status artifact.
 */
$repoRoot = dirname(__DIR__, 2);

$statusPath = $repoRoot . '/delivery/canon/tagging-canon-status.json';
if (!is_file($statusPath)) {
    fwrite(STDERR, "Missing machine-readable Tagging canon status.\n");

    exit(1);
}

$status = json_decode((string) file_get_contents($statusPath), true);
if (!is_array($status)) {
    fwrite(STDERR, "Tagging canon status is not valid JSON.\n");

    exit(1);
}

$required = [
    ['component', 'Tagging'],
    ['component_namespace', 'App\\Tagging\\'],
    ['plain_app_namespace_forbidden', true],
];

foreach ($required as [$key, $expected]) {
    if (!array_key_exists($key, $status) || $status[$key] !== $expected) {
        fwrite(STDERR, sprintf("Invalid canon status key `%s`.\n", $key));

        exit(1);
    }
}

$workflow = $status['workflow'] ?? null;
if (!is_array($workflow)) {
    fwrite(STDERR, "Missing workflow section.\n");

    exit(1);
}

if (($workflow['patch_mode'] ?? null) !== 'touched-files only') {
    fwrite(STDERR, "Workflow must require touched-files only.\n");

    exit(1);
}

foreach (['cumulative_snapshot_application', 'repository_wide_cleanup', 'full_repository_overwrite'] as $flag) {
    if (($workflow[$flag] ?? true) !== false) {
        fwrite(STDERR, sprintf("Workflow flag `%s` must be false.\n", $flag));

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

echo "Tagging machine-readable canon status audit passed.\n";
