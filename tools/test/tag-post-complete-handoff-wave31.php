<?php

declare(strict_types=1);

/**
 * Verifies the Tagging post-complete handoff marker.
 */
$repoRoot = dirname(__DIR__, 2);

$handoffPath = $repoRoot . '/delivery/canon/tagging-post-complete-handoff.json';
if (!is_file($handoffPath)) {
    fwrite(STDERR, "Missing Tagging post-complete handoff marker.\n");
    exit(1);
}

$handoff = json_decode((string) file_get_contents($handoffPath), true);
if (!is_array($handoff)) {
    fwrite(STDERR, "Tagging post-complete handoff marker is not valid JSON.\n");
    exit(1);
}

if (($handoff['component'] ?? null) !== 'Tagging') {
    fwrite(STDERR, "Handoff component must be Tagging.\n");
    exit(1);
}

if (($handoff['component_namespace'] ?? null) !== 'App\\Tagging\\') {
    fwrite(STDERR, "Handoff must preserve App\\Tagging\\ namespace.\n");
    exit(1);
}

if (($handoff['after_wave'] ?? null) !== 30) {
    fwrite(STDERR, "Handoff must follow Wave 30.\n");
    exit(1);
}

$policy = $handoff['next_work_policy'] ?? null;
if (!is_array($policy)) {
    fwrite(STDERR, "Handoff is missing next_work_policy.\n");
    exit(1);
}

foreach (['continue_only_from_local_failures', 'require_current_slice_for_new_structural_work', 'preserve_component_namespace', 'touched_files_only', 'repository_wide_cleanup_forbidden', 'full_repository_overwrite_forbidden'] as $flag) {
    if (($policy[$flag] ?? false) !== true) {
        fwrite(STDERR, sprintf("Handoff policy flag `%s` must be true.\n", $flag));
        exit(1);
    }
}

$requiredFiles = [
    'delivery/canon/tagging-canonicalization-complete.json',
    'delivery/canon/tagging-verification-profile.json',
    'delivery/canon/tagging-canon-status.json',
    'tools/test/tag-ci-bridge-wave27.php',
    'tools/test/tag-ci-bridge-wave28.ps1',
    'tools/test/tag-ci-bridge-wave28.sh',
];

foreach ($requiredFiles as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        fwrite(STDERR, sprintf("Required handoff dependency is missing: %s\n", $relativePath));
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

echo "Tagging post-complete handoff audit passed.\n";
