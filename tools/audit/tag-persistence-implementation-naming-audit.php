<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$forbidden = [
    'src/Infrastructure/Persistence/Tag/DoctrineTagEntityRepository.php',
    'src/Infrastructure/Persistence/Tag/DoctrineTagRepository.php',
    'src/Infrastructure/Persistence/Tag/InMemoryTagRepository.php',
];

$required = [
    'src/Infrastructure/Persistence/Tag/TagDoctrineEntityRepository.php',
    'src/Infrastructure/Persistence/Tag/TagDoctrineRepository.php',
    'src/Infrastructure/Persistence/Tag/TagInMemoryRepository.php',
];

$errors = [];

foreach ($forbidden as $relative) {
    if (is_file($root . DIRECTORY_SEPARATOR . $relative)) {
        $errors[] = 'Legacy persistence implementation file must be removed: ' . $relative;
    }
}

foreach ($required as $relative) {
    if (!is_file($root . DIRECTORY_SEPARATOR . $relative)) {
        $errors[] = 'Canonical persistence implementation file is missing: ' . $relative;
    }
}

$scanRoots = [
    $root . DIRECTORY_SEPARATOR . 'src',
    $root . DIRECTORY_SEPARATOR . 'tests',
    $root . DIRECTORY_SEPARATOR . 'config',
    $root . DIRECTORY_SEPARATOR . 'docs',
    $root . DIRECTORY_SEPARATOR . 'release',
    $root . DIRECTORY_SEPARATOR . 'migration',
];

$legacySymbols = [
    'DoctrineTagEntityRepository',
    'DoctrineTagRepository',
    'InMemoryTagRepository',
];

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['php', 'yaml', 'yml', 'md', 'json', 'xml', 'adoc'], true)) {
            continue;
        }

        $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
        if ('docs/architecture/tagging-wave6-persistence-implementation-naming-cleanup.md' === str_replace('\\', '/', $relativePath)) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if (!is_string($contents)) {
            continue;
        }

        foreach ($legacySymbols as $legacySymbol) {
            if (str_contains($contents, $legacySymbol)) {
                $errors[] = sprintf(
                    'Legacy persistence implementation symbol %s remains in %s',
                    $legacySymbol,
                    $relativePath,
                );
            }
        }
    }
}

if ([] !== $errors) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo 'Tagging persistence implementation naming audit passed.' . PHP_EOL;
