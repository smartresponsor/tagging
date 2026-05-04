<?php

declare(strict_types=1);

/**
 * Verifies the Tagging post-canon verification profile.
 */
$repoRoot = dirname(__DIR__, 2);

$profilePath = $repoRoot . '/delivery/canon/tagging-verification-profile.json';
if (!is_file($profilePath)) {
    fwrite(STDERR, "Missing Tagging verification profile.\n");
    exit(1);
}

$profile = json_decode((string) file_get_contents($profilePath), true);
if (!is_array($profile)) {
    fwrite(STDERR, "Tagging verification profile is not valid JSON.\n");
    exit(1);
}

if (($profile['component'] ?? null) !== 'Tagging') {
    fwrite(STDERR, "Verification profile component must be Tagging.\n");
    exit(1);
}

if (($profile['component_namespace'] ?? null) !== 'App\\Tagging\\') {
    fwrite(STDERR, "Verification profile must preserve App\\Tagging\\ namespace.\n");
    exit(1);
}

$entrypoints = $profile['entrypoints'] ?? null;
if (!is_array($entrypoints)) {
    fwrite(STDERR, "Verification profile is missing entrypoints.\n");
    exit(1);
}

foreach (['ci_bridge', 'ci_bridge_powershell', 'ci_bridge_bash', 'canon_status', 'health', 'complete_post_canon'] as $key) {
    $relativePath = $entrypoints[$key] ?? null;
    if (!is_string($relativePath) || $relativePath === '') {
        fwrite(STDERR, sprintf("Verification profile is missing entrypoint `%s`.\n", $key));
        exit(1);
    }

    if (!is_file($repoRoot . '/' . $relativePath)) {
        fwrite(STDERR, sprintf("Verification profile entrypoint `%s` is missing file: %s\n", $key, $relativePath));
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

echo "Tagging verification profile audit passed.\n";
