<?php

declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';

$errors = [];

foreach ([
    'src/Kernel.php',
    'config/bootstrap.php',
    'config/services.yaml',
    'config/routes.yaml',
    'public/index.php',
    'bin/console',
] as $path) {
    if (!is_file($root . '/' . $path)) {
        $errors[] = 'missing Symfony bootstrap file ' . $path;
    }
}

$services = file_get_contents($root . '/config/services.yaml');
if (!is_string($services)) {
    $errors[] = 'missing config/services.yaml';
} else {
    foreach ([
        'services/infrastructure.yaml',
        'services/cache.yaml',
        'services/read_model.yaml',
        'services/application.yaml',
        'services/http.yaml',
        'services/ops.yaml',
        'services/core.yaml',
        'services/tagging.yaml',
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

echo "tag-bootstrap-audit: ok" . PHP_EOL;
