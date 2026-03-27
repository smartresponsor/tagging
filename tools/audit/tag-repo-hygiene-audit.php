<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$forbiddenRootFiles = [
    'MANIFEST.wave-02.json',
    'MANIFEST.wave-03.json',
    'MANIFEST.wave-04.json',
    'ZZ_CHANGED_FILES.txt',
    'ZZ_MOVE_MAP.txt',
    'ZZ_NEXT.txt',
    'ZZ_REMOVED_FILES.txt',
    'ZZ_REMOVE_EMPTY_DIRS.txt',
    'ZZ_WAVE.txt',
];

$forbiddenRootDirectories = [
    'tag_cons_patched',
    'tag_fix',
    'tmp',
];

foreach ($forbiddenRootFiles as $relativePath) {
    if (file_exists($root . '/' . $relativePath)) {
        $errors[] = sprintf('transport artifact must not live in repository root: %s', $relativePath);
    }
}

foreach ($forbiddenRootDirectories as $relativePath) {
    if (is_dir($root . '/' . $relativePath)) {
        $errors[] = sprintf('transport workspace directory must not live in repository root: %s', $relativePath);
    }
}

$requiredDocs = [
    'docs/ops/ci-gates.md',
    'docs/ops/install-test-gates.md',
    'docs/ops/repo-hygiene.md',
];

foreach ($requiredDocs as $relativePath) {
    if (!is_file($root . '/' . $relativePath)) {
        $errors[] = sprintf('required ops document is missing: %s', $relativePath);
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, '[repo-hygiene] ' . $error . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, '[repo-hygiene] OK
');
