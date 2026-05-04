<?php

declare(strict_types=1);

/**
 * Aggregates the Tagging canonicalization milestone after Waves 1-17.
 *
 * This gate does not replace focused audits. It verifies that the canonical
 * cleanup surface remains wired and that the retired generic artifacts do not
 * return.
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

if (str_contains($composer, '"App\\\\": "src/"')) {
    fwrite(STDERR, "composer.json must not collapse Tagging to plain App\\ namespace.\n");

    exit(1);
}

$requiredAudits = [
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
];

$requiredCanonicalPaths = [
    'composer.json',
    'src',
    'deploy/docker/compose.yaml',
    'deploy/docker/entrypoint.sh',
    'deploy/docker/host.Dockerfile',
    'admin/index.html',
    'admin/tag-admin.js',
    'admin/tag-admin.css',
    'delivery/rc/tag-rc-manifest.yaml',
    'public/tag/demo/tag-demo-requests.http',
    'public/tag/examples/tag-http-examples.http',
    'public/tag/examples/tag-seed-examples.http',
    'public/tag/examples/tag-tour-examples.http',
    'sdk/php/tag/TagClient.php',
    'sdk/ts/tag/tag-client.ts',
    'tools/tag-bootstrap.php',
    'tools/cli/tag-cli.php',
    'tools/tag-migration-smoke.sh',
    'tools/tag-migration-smoke.ps1',
    'tools/tag-test-db-start.sh',
    'tools/tag-test-db-stop.sh',
    'tools/tag-webhook-worker.php',
    'tools/smoke/tag-smoke.sh',
    'tools/synthetic/tag-slo.sh',
    'tools/test-db/tag-compose.yaml',
];

$forbiddenPaths = [
    'docker-compose.yml',
    'host/Dockerfile',
    'Tagging',
    'admin/app.js',
    'admin/style.css',
    'delivery/rc/manifest.yaml',
    'public/tag/demo/requests.http',
    'public/tag/examples/http.http',
    'public/tag/examples/seed.http',
    'public/tag/examples/tour.http',
    'sdk/php/tag/Client.php',
    'sdk/ts/tag/client.ts',
    'tools/_bootstrap.php',
    'tools/cli/tag.php',
    'tools/lint.php',
    'tools/git/install-hooks.php',
    'tools/local/panther-test.sh',
    'tools/local/php-extension-doctor.sh',
    'tools/migration-smoke.sh',
    'tools/migration-smoke.ps1',
    'tools/test-db-start.sh',
    'tools/test-db-stop.sh',
    'tools/webhook_worker.php',
    'tools/db/migrate.php',
    'tools/db/migrate.sh',
    'tools/smoke/smoke.sh',
    'tools/smoke/tag_smoke.sh',
    'tools/synthetic/slo.sh',
    'tools/test-db/docker-compose.yml',
];

$violations = [];

foreach ($requiredAudits as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required wave audit missing: ' . $relativePath;
    }
}

foreach ($requiredCanonicalPaths as $relativePath) {
    $absolutePath = $repoRoot . '/' . $relativePath;
    if (!file_exists($absolutePath)) {
        $violations[] = 'required canonical path missing: ' . $relativePath;
    }
}

foreach ($forbiddenPaths as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'retired generic/legacy path returned: ' . $relativePath;
    }
}

$srcIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($repoRoot . '/src', FilesystemIterator::SKIP_DOTS),
);

foreach ($srcIterator as $fileInfo) {
    if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }

    $relativePath = substr($fileInfo->getPathname(), strlen($repoRoot) + 1);
    $content = (string) file_get_contents($fileInfo->getPathname());

    $hasRootNamespace = str_contains($content, 'namespace App\\Tagging;');
    $hasNestedNamespace = str_contains($content, 'namespace App\\Tagging\\');

    if (!$hasRootNamespace && !$hasNestedNamespace) {
        $violations[] = 'source file outside App\\Tagging namespace: ' . $relativePath;
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging canonical milestone audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging canonical milestone audit passed.\n";
