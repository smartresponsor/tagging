<?php

declare(strict_types=1);

$root = require __DIR__ . '/../tag-bootstrap.php';

$errors = [];

foreach ([
    'config/services.yaml',
    'config/routes.yaml',
    'src/TaggingBundle.php',
    'src/Kernel.php',
    'config/bundles.php',
    'bin/console',
] as $path) {
    if (!is_file($root . '/' . $path)) {
        $errors[] = 'missing dual-runtime bootstrap file ' . $path;
    }
}

foreach ([
    'config/bootstrap.php',
    'public/index.php',
] as $path) {
    if (is_file($root . '/' . $path)) {
        $errors[] = 'non-canonical standalone runtime file must be removed: ' . $path;
    }
}

$services = file_get_contents($root . '/config/services.yaml');
if (!is_string($services)) {
    $errors[] = 'missing config/services.yaml';
} else {
    foreach ([
        'services/tag_infrastructure.yaml',
        'services/cache.yaml',
        'services/tag_read_model.yaml',
        'services/tag_application.yaml',
        'services/tag_http.yaml',
        'services/tag_ops.yaml',
        'services/tag_core.yaml',
        'services/tag_services.yaml',
    ] as $import) {
        if (!str_contains($services, $import)) {
            $errors[] = 'missing service import ' . $import;
        }
    }
}

if (is_dir($root . '/host-minimal')) {
    $errors[] = 'host-minimal directory must not be active runtime surface';
}

if ([] !== $errors) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo 'tag-bootstrap-audit: ok' . PHP_EOL;
