<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$forbiddenFiles = [
    'tools/lint.php',
    'tools/db/migrate.php',
    'tools/git/install-hooks.php',
    'tools/local/panther-test.sh',
    'tools/local/php-extension-doctor.sh',
    'tools/smoke/tag_tag-smoke.sh',
];

$requiredFiles = [
    'tools/tag-lint.php',
    'tools/db/tag-migrate.php',
    'tools/git/tag-install-hooks.php',
    'tools/local/tag-panther-test.sh',
    'tools/local/tag-php-extension-doctor.sh',
    'tools/smoke/tag-tag-smoke.sh',
];

$errors = [];

foreach ($forbiddenFiles as $relativePath) {
    if (file_exists($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        $errors[] = 'Forbidden legacy tooling entrypoint remains: ' . $relativePath;
    }
}

foreach ($requiredFiles as $relativePath) {
    if (!is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        $errors[] = 'Required canonical tooling entrypoint is missing: ' . $relativePath;
    }
}

$composerPath = $root . DIRECTORY_SEPARATOR . 'composer.json';
$composerJson = is_file($composerPath) ? (string) file_get_contents($composerPath) : '';
$forbiddenComposerReferences = [
    'tools/lint.php',
    'tools/git/install-hooks.php',
    'tools/local/panther-test.sh',
    'tools/local/php-extension-doctor.sh',
];

foreach ($forbiddenComposerReferences as $reference) {
    if (str_contains($composerJson, $reference)) {
        $errors[] = 'composer.json still references legacy tooling entrypoint: ' . $reference;
    }
}

$requiredComposerReferences = [
    'tools/tag-lint.php',
    'tools/git/tag-install-hooks.php',
    'tools/local/tag-panther-test.sh',
    'tools/local/tag-php-extension-doctor.sh',
];

foreach ($requiredComposerReferences as $reference) {
    if (!str_contains($composerJson, $reference)) {
        $errors[] = 'composer.json does not reference canonical tooling entrypoint: ' . $reference;
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "tag-tooling-entrypoint-audit: ok
";
