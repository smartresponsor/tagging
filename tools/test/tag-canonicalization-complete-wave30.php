<?php

declare(strict_types=1);

/**
 * Verifies the Tagging canonicalization completion marker.
 */
$repoRoot = dirname(__DIR__, 2);

$completePath = $repoRoot . '/delivery/canon/tagging-canonicalization-complete.json';
if (!is_file($completePath)) {
    fwrite(STDERR, "Missing Tagging canonicalization completion marker.\n");
    exit(1);
}

$complete = json_decode((string) file_get_contents($completePath), true);
if (!is_array($complete)) {
    fwrite(STDERR, "Tagging canonicalization completion marker is not valid JSON.\n");
    exit(1);
}

$requiredEquals = [
    'component' => 'Tagging',
    'status' => 'post-canon-complete',
    'component_namespace' => 'App\\Tagging\\',
    'completed_wave' => 30,
];

foreach ($requiredEquals as $key => $expected) {
    if (($complete[$key] ?? null) !== $expected) {
        fwrite(STDERR, sprintf("Invalid completion marker key `%s`.\n", $key));
        exit(1);
    }
}

foreach (['canon_status', 'verification_profile', 'ci_bridge', 'ci_bridge_powershell', 'ci_bridge_bash', 'maintenance_playbook'] as $key) {
    $relativePath = $complete[$key] ?? null;
    if (!is_string($relativePath) || $relativePath === '' || !is_file($repoRoot . '/' . $relativePath)) {
        fwrite(STDERR, sprintf("Completion marker points to missing file for `%s`.\n", $key));
        exit(1);
    }
}

$rules = $complete['rules'] ?? null;
if (!is_array($rules)) {
    fwrite(STDERR, "Completion marker is missing rules.\n");
    exit(1);
}

foreach (['preserve_component_namespace', 'plain_app_namespace_forbidden', 'touched_files_only', 'repository_wide_cleanup_forbidden', 'full_repository_overwrite_forbidden', 'cumulative_snapshot_application_forbidden'] as $flag) {
    if (($rules[$flag] ?? false) !== true) {
        fwrite(STDERR, sprintf("Completion marker rule `%s` must be true.\n", $flag));
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

echo "Tagging canonicalization completion marker audit passed.\n";
