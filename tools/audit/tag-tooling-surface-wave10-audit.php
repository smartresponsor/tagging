<?php

declare(strict_types=1);

/**
 * Ensures Tagging tooling entrypoints use component-scoped names and no generic
 * executable/tooling residue remains in the repository.
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

$forbidden = [
    'tools/migration-smoke.ps1',
    'tools/migration-smoke.sh',
    'tools/test-db-start.sh',
    'tools/test-db-stop.sh',
    'tools/webhook_worker.php',
    'tools/db/migrate.sh',
    'tools/migration/apply-symfony-native-target.php',
    'tools/slugify/slugify.php',
    'tools/smoke/smoke.sh',
    'tools/synthetic/slo.sh',
    'tools/test-db/docker-compose.yml',
];

$required = [
    'tools/tag-migration-smoke.ps1',
    'tools/tag-migration-smoke.sh',
    'tools/tag-test-db-start.sh',
    'tools/tag-test-db-stop.sh',
    'tools/tag-webhook-worker.php',
    'tools/db/tag-migrate.sh',
    'tools/migration/tag-apply-symfony-native-target.php',
    'tools/slugify/tag-slugify.php',
    'tools/smoke/tag-smoke.sh',
    'tools/synthetic/tag-slo.sh',
    'tools/test-db/tag-compose.yaml',
];

$violations = [];
foreach ($forbidden as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'forbidden still exists: ' . $relativePath;
    }
}

foreach ($required as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required canonical entrypoint missing: ' . $relativePath;
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging tooling surface audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging tooling surface audit passed.\n";
