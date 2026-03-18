<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$violations = [];

$forbiddenRootPatterns = [
    'MANIFEST.wave-*.json',
    'ZZ_*',
];

foreach ($forbiddenRootPatterns as $pattern) {
    $matches = glob($root . DIRECTORY_SEPARATOR . $pattern, GLOB_NOSORT) ?: [];
    foreach ($matches as $match) {
        $violations[] = basename($match);
    }
}

$forbiddenRootDirectories = [
    'tag_cons_patched',
    'tag_fix',
    'tmp',
];

foreach ($forbiddenRootDirectories as $directory) {
    if (is_dir($root . DIRECTORY_SEPARATOR . $directory)) {
        $violations[] = $directory . '/';
    }
}

sort($violations);
$violations = array_values(array_unique($violations));

if ($violations !== []) {
    fwrite(STDERR, "Snapshot purity audit failed. Forbidden transport artifacts detected at repository root:
");
    foreach ($violations as $violation) {
        fwrite(STDERR, " - {$violation}
");
    }
    exit(1);
}

fwrite(STDOUT, "Snapshot purity audit passed. Repository root is free of wave/transport artifacts and transient workspaces.
");
