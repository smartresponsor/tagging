<?php

declare(strict_types=1);

/**
 * Runs the complete Tagging post-canonicalization local verification chain.
 *
 * Wave 20 runs direct audit scripts.
 * Wave 21 runs PHPUnit wrappers for the post-canon audit gates.
 * Wave 22 chains both runners as one local command.
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

$runners = [
    'tools/audit/tag-post-canon-verification-wave20.php',
    'tools/test/tag-post-canon-tests-wave21.php',
];

$failed = [];

foreach ($runners as $runner) {
    $absolutePath = $repoRoot . '/' . $runner;

    if (!is_file($absolutePath)) {
        $failed[] = $runner . ' [missing]';
        fwrite(STDERR, "[missing] {$runner}\n");

        continue;
    }

    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($absolutePath);
    $output = [];
    $exitCode = 0;

    echo "[run] {$runner}\n";
    exec($command . ' 2>&1', $output, $exitCode);

    foreach ($output as $line) {
        echo "  {$line}\n";
    }

    if ($exitCode !== 0) {
        $failed[] = $runner . ' [exit ' . $exitCode . ']';
        fwrite(STDERR, "[fail] {$runner}\n");

        continue;
    }

    echo "[ok] {$runner}\n";
}

if ($failed !== []) {
    fwrite(STDERR, "\nTagging complete post-canon verification failed:\n");
    foreach ($failed as $failure) {
        fwrite(STDERR, ' - ' . $failure . "\n");
    }

    exit(1);
}

echo "\nTagging complete post-canon verification passed.\n";
