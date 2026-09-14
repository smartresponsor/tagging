<?php

declare(strict_types=1);

$root = require __DIR__ . '/../tag-bootstrap.php';
$composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$errors = [];

if (!isset($composer['require']['cruding/crud'])) {
    $errors[] = 'cruding/crud must be a runtime dependency';
}

$forbiddenRouteSources = [
    'config/component/routes.yaml',
    'config/platform/routes/crud/tag.yaml',
];
foreach ($forbiddenRouteSources as $relativePath) {
    if (is_file($root . '/' . $relativePath)) {
        $errors[] = 'local generic CRUD route source remains: ' . $relativePath;
    }
}

$controllerRoot = $root . '/src/Controller';
if (is_dir($controllerRoot)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerRoot));
    foreach ($iterator as $file) {
        if ($file->isFile() && 'php' === strtolower($file->getExtension()) && str_contains($file->getFilename(), 'Crud')) {
            $errors[] = 'local generic CRUD controller remains: ' . $file->getPathname();
        }
    }
}

if ([] !== $errors) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "tag-route-controller-audit: ok\n";
