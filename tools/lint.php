<?php

declare(strict_types=1);

$roots = [
    'src',
    'tests',
    'tools',
    'config',
    'bin',
    'public',
];

$rootDir = getcwd();
if (false === $rootDir || '' === $rootDir) {
    fwrite(STDERR, "Cannot determine working directory.\n");
    exit(1);
}

$phpBinary = PHP_BINARY;
$exitCode = 0;

foreach ($roots as $root) {
    $path = $rootDir . DIRECTORY_SEPARATOR . $root;
    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo) {
            continue;
        }

        if ('php' !== strtolower($file->getExtension())) {
            continue;
        }

        $command = escapeshellarg($phpBinary) . ' -l ' . escapeshellarg($file->getPathname());
        passthru($command, $fileExitCode);

        if (0 !== $fileExitCode) {
            $exitCode = $fileExitCode;
        }
    }
}

exit($exitCode);
