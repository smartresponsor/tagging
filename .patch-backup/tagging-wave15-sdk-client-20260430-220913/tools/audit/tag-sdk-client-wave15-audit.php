<?php

declare(strict_types=1);

/**
 * Ensures published Tagging SDK clients use component-scoped names instead of
 * generic Client/client entrypoints.
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
    'sdk/php/tag/Client.php',
    'sdk/ts/tag/client.ts',
];

$required = [
    'sdk/php/tag/TagClient.php',
    'sdk/ts/tag/tag-client.ts',
];

$violations = [];
foreach ($forbidden as $relativePath) {
    if (file_exists($repoRoot . '/' . $relativePath)) {
        $violations[] = 'forbidden generic SDK client still exists: ' . $relativePath;
    }
}

foreach ($required as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required canonical SDK client missing: ' . $relativePath;
    }
}

$phpClientPath = $repoRoot . '/sdk/php/tag/TagClient.php';
if (is_file($phpClientPath)) {
    $phpClient = (string) file_get_contents($phpClientPath);
    if (!str_contains($phpClient, 'class TagClient')) {
        $violations[] = 'PHP SDK client file must declare TagClient';
    }

    if (str_contains($phpClient, 'class Client')) {
        $violations[] = 'PHP SDK client file still declares generic Client';
    }
}

$tsClientPath = $repoRoot . '/sdk/ts/tag/tag-client.ts';
if (is_file($tsClientPath)) {
    $tsClient = (string) file_get_contents($tsClientPath);
    if (!str_contains($tsClient, 'TagClient')) {
        $violations[] = 'TypeScript SDK client must expose TagClient';
    }

    if (str_contains($tsClient, 'class Client')) {
        $violations[] = 'TypeScript SDK client still declares generic Client';
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Tagging SDK client audit failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }

    exit(1);
}

echo "Tagging SDK client audit passed.\n";
