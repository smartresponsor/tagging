<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/tests', FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    $basename = $file->getBasename('.php');

    if ($basename === 'TagDoctrineEntityManagerFactory') {
        continue;
    }

    if (!str_starts_with($basename, 'Tag')) {
        $errors[] = sprintf('test file must use Tag prefix: %s', $relativePath);
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    if ($contents === false) {
        $errors[] = sprintf('test file is not readable: %s', $relativePath);
        continue;
    }

    if (preg_match('/\b(?:final\s+)?(?:abstract\s+)?(?:class|trait|interface)\s+(\w+)/', $contents, $matches) === 1) {
        $symbol = $matches[1];
        if (!str_starts_with($symbol, 'Tag')) {
            $errors[] = sprintf('test symbol must use Tag prefix: %s declares %s', $relativePath, $symbol);
        }
    }
}

$forbiddenRootArtifacts = [
    'Tagging',
];

foreach ($forbiddenRootArtifacts as $relativePath) {
    if (file_exists($root . '/' . $relativePath)) {
        $errors[] = sprintf('legacy root executable must not remain: %s', $relativePath);
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, '[tag-test-class-form] ' . $error . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, '[tag-test-class-form] OK' . PHP_EOL);
