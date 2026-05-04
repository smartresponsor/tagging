<?php

declare(strict_types=1);

$root = require __DIR__ . '/../tag-bootstrap.php';
$runtime = require $root . '/config/tag_runtime.php';

if (!is_array($runtime) || 'hosted-package' !== ($runtime['runtime'] ?? null)) {
    echo 'tag-bootstrap-runtime-audit: failed' . PHP_EOL;
    exit(1);
}

echo 'tag-bootstrap-runtime-audit: ok' . PHP_EOL;
