<?php

declare(strict_types=1);

/**
 * CI-friendly bridge runner for the Tagging post-canon verification surface.
 *
 * It validates the machine-readable canon status first, then the post-canon
 * health check, then the complete post-canon runner.
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

$steps = [
    'tools/test/tag-canon-status-wave26.php',
    'tools/test/tag-post-canon-health-wave24.php',
    'tools/test/tag-post-canon-all-wave22.php',
];

$failed = [];

foreach ($steps as $step) {
    $absolutePath = $repoRoot . '/' . $step;
    if (!is_file($absolutePath)) {
        $failed[] = $step . ' [missing]';
        fwrite(STDERR, "[missing] {$step}\n");

        continue;
    }

    echo "[run] {$step}\n";
    $output = [];
    $exitCode = 0;
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($absolutePath) . ' 2>&1', $output, $exitCode);

    foreach ($output as $line) {
        echo "  {$line}\n";
    }

    if ($exitCode !== 0) {
        $failed[] = $step . ' [exit ' . $exitCode . ']';
        fwrite(STDERR, "[fail] {$step}\n");

        continue;
    }

    echo "[ok] {$step}\n";
}

if ($failed !== []) {
    fwrite(STDERR, "\nTagging CI bridge failed:\n");
    foreach ($failed as $failure) {
        fwrite(STDERR, ' - ' . $failure . "\n");
    }

    exit(1);
}

echo "\nTagging CI bridge passed.\n";
