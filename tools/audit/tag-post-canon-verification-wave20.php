<?php

declare(strict_types=1);

/**
 * Runs the Tagging post-canonicalization verification audit set in a stable order.
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

$audits = [
    'tools/audit/tag-class-form-audit.php',
    'tools/audit/tag-service-depth-audit.php',
    'tools/audit/tag-legacy-facade-audit.php',
    'tools/audit/tag-legacy-duplicate-surface-audit.php',
    'tools/audit/tag-persistence-implementation-naming-audit.php',
    'tools/audit/tag-test-class-form-audit.php',
    'tools/audit/tag-tooling-entrypoint-audit.php',
    'tools/audit/tag-duplicate-residue-audit.php',
    'tools/audit/tag-tooling-surface-wave10-audit.php',
    'tools/audit/tag-audit-stabilization-wave11-audit.php',
    'tools/audit/tag-cli-bootstrap-wave12-audit.php',
    'tools/audit/tag-admin-asset-wave13-audit.php',
    'tools/audit/tag-public-example-wave14-audit.php',
    'tools/audit/tag-sdk-client-wave15-audit.php',
    'tools/audit/tag-delivery-manifest-wave16-audit.php',
    'tools/audit/tag-conventional-artifact-wave17-audit.php',
    'tools/audit/tag-canon-milestone-wave18-audit.php',
    'tools/audit/tag-canonicalization-review-wave19-audit.php',
];

$failed = [];

foreach ($audits as $audit) {
    $absolutePath = $repoRoot . '/' . $audit;
    if (!is_file($absolutePath)) {
        $failed[] = $audit . ' [missing]';
        fwrite(STDERR, "[missing] {$audit}\n");

        continue;
    }

    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($absolutePath);
    $output = [];
    $exitCode = 0;

    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        $failed[] = $audit . ' [exit ' . $exitCode . ']';
        fwrite(STDERR, "[fail] {$audit}\n");
        foreach ($output as $line) {
            fwrite(STDERR, "  {$line}\n");
        }

        continue;
    }

    echo "[ok] {$audit}\n";
}

if ($failed !== []) {
    fwrite(STDERR, "\nTagging post-canon verification failed:\n");
    foreach ($failed as $failure) {
        fwrite(STDERR, ' - ' . $failure . "\n");
    }

    exit(1);
}

echo "\nTagging post-canon verification passed.\n";
