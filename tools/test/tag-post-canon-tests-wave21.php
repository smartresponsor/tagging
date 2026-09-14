<?php

declare(strict_types=1);

/**
 * Runs post-canonicalization PHPUnit gate classes in a stable order.
 *
 * This runner is intentionally separate from tag-post-canon-verification-wave20.php,
 * which runs audit scripts directly. Wave 21 focuses on PHPUnit wrappers so the
 * local repository can verify both audit-script execution and PHPUnit wiring.
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

$phpunitCandidates = [
    $repoRoot . '/vendor/bin/phpunit',
    $repoRoot . '/vendor/bin/phpunit.bat',
];

$phpunit = null;
foreach ($phpunitCandidates as $candidate) {
    if (is_file($candidate)) {
        $phpunit = $candidate;
        break;
    }
}

if ($phpunit === null) {
    fwrite(STDERR, "Unable to locate vendor/bin/phpunit. Run composer install first.\n");

    exit(1);
}

$tests = [
    'tests/TagClassNamingWave2AuditTest.php',
    'tests/TagServiceDepthWave3AuditTest.php',
    'tests/TagLegacyFacadeWave4AuditTest.php',
    'tests/TagLegacyDuplicateSurfaceWave5AuditTest.php',
    'tests/TagPersistenceImplementationNamingWave6AuditTest.php',
    'tests/TagTestClassFormWave7AuditTest.php',
    'tests/TagToolingEntrypointWave8AuditTest.php',
    'tests/TagDuplicateResidueWave9AuditTest.php',
    'tests/TagToolingSurfaceWave10AuditTest.php',
    'tests/TagAuditStabilizationWave11AuditTest.php',
    'tests/TagCliBootstrapWave12AuditTest.php',
    'tests/TagAdminAssetWave13AuditTest.php',
    'tests/TagPublicExampleWave14AuditTest.php',
    'tests/TagSdkClientWave15AuditTest.php',
    'tests/TagDeliveryManifestWave16AuditTest.php',
    'tests/TagConventionalArtifactWave17AuditTest.php',
    'tests/TagCanonMilestoneWave18AuditTest.php',
    'tests/TagCanonicalizationReviewWave19AuditTest.php',
    'tests/TagPostCanonVerificationWave20Test.php',
];

$failed = [];

foreach ($tests as $test) {
    $absolutePath = $repoRoot . '/' . $test;
    if (!is_file($absolutePath)) {
        $failed[] = $test . ' [missing]';
        fwrite(STDERR, "[missing] {$test}\n");

        continue;
    }

    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($phpunit) . ' ' . escapeshellarg($absolutePath);
    $output = [];
    $exitCode = 0;

    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        $failed[] = $test . ' [exit ' . $exitCode . ']';
        fwrite(STDERR, "[fail] {$test}\n");
        foreach ($output as $line) {
            fwrite(STDERR, "  {$line}\n");
        }

        continue;
    }

    echo "[ok] {$test}\n";
}

if ($failed !== []) {
    fwrite(STDERR, "\nTagging post-canon PHPUnit runner failed:\n");
    foreach ($failed as $failure) {
        fwrite(STDERR, ' - ' . $failure . "\n");
    }

    exit(1);
}

echo "\nTagging post-canon PHPUnit runner passed.\n";
