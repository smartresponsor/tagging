<?php

declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$source = $root . '/contracts/http/tag-openapi.yaml';
$targetDir = $root . '/public/tag/openapi';
$target = $targetDir . '/tag-openapi.yaml';

if (!is_file($source)) {
    fwrite(STDERR, "Missing source OpenAPI contract.\n");
    exit(1);
}

if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
    fwrite(STDERR, "Unable to create generated OpenAPI target directory.\n");
    exit(1);
}

if (false === copy($source, $target)) {
    fwrite(STDERR, "Unable to publish generated OpenAPI artifact.\n");
    exit(1);
}

echo "tag-openapi-publish: ok\n";
