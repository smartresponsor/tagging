<?php

declare(strict_types=1);

/**
 * Documents and enforces the final boundary between allowed framework/static
 * convention files and forbidden generic residue in the Tagging component.
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

$allowedConventionalArtifacts = [
    'admin/index.html',
    'config/routes.yaml',
    'config/services.yaml',
    'config/component/api_platform.yaml',
    'config/component/component.yaml',
    'config/component/doctrine.yaml',
    'config/component/env.yaml',
    'config/component/messenger.yaml',
    'config/component/routes.yaml',
    'config/component/security.yaml',
    'config/component/services.yaml',
    'config/component/smoke.yaml',
    'config/services/application.yaml',
    'config/services/cache.yaml',
    'config/services/core.yaml',
    'config/services/http.yaml',
    'config/services/infrastructure.yaml',
    'config/services/ops.yaml',
    'config/services/read_model.yaml',
    'docs/README.md',
    'docs/admin/README.md',
    'sdk/README.md',
    'public/tag/openapi/index.html',
    'migration/symfony-native-target/composer.json',
    'migration/symfony-native-target/public/index.php',
];

$forbiddenGenericResidue = [
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

foreach ($allowedConventionalArtifacts as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'documented conventional artifact missing: ' . $relativePath;
    }
}

foreach ($forbiddenGenericResidue as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'forbidden generic residue returned: ' . $relativePath;
    }
}

$remainingGenericFiles = [];
$scanRoots = [
    'admin',
    'config',
    'delivery',
    'docs',
    'migration',
    'public',
    'sdk',
    'tools',
];

$genericNames = [
    'app.js',
    'style.css',
    'manifest.yaml',
    'requests.http',
    'http.http',
    'seed.http',
    'tour.http',
    'Client.php',
    'client.ts',
    '_bootstrap.php',
    'tag.php',
    'lint.php',
    'install-hooks.php',
    'panther-test.sh',
    'php-extension-doctor.sh',
    'migration-smoke.sh',
    'migration-smoke.ps1',
    'test-db-start.sh',
    'test-db-stop.sh',
    'webhook_worker.php',
    'migrate.php',
    'migrate.sh',
    'smoke.sh',
    'slo.sh',
    'docker-compose.yml',
];

foreach ($scanRoots as $scanRoot) {
    $absoluteRoot = $repoRoot . '/' . $scanRoot;
    if (!is_dir($absoluteRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absoluteRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
            continue;
        }

        $relativePath = substr($fileInfo->getPathname(), strlen($repoRoot) + 1);
        $fileName = $fileInfo->getFilename();

        if (!in_array($fileName, $genericNames, true)) {
            continue;
        }

        if (in_array($relativePath, $allowedConventionalArtifacts, true)) {
            continue;
        }

        if ($relativePath === 'tools/audit/tag-conventional-artifact-wave17-audit.php') {
            continue;
        }

        $remainingGenericFiles[] = $relativePath;
    }
}

foreach ($remainingGenericFiles as $relativePath) {
    $violations[] = 'unclassified generic artifact remains outside allowed boundary: ' . $relativePath;
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging conventional artifact boundary audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging conventional artifact boundary audit passed.\n";
