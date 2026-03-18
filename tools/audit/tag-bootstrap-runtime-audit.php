<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = require __DIR__ . '/../_bootstrap.php';
$bootstrap = file_get_contents($root . '/host-minimal/bootstrap.php');
if (!is_string($bootstrap) || $bootstrap === '') {
    fwrite(STDERR, "missing bootstrap file\n");
    exit(1);
}

$errors = [];
if (!str_contains($bootstrap, 'use App\\Infrastructure\\ReadModel\\Tag\\TagReadModel;')) {
    $errors[] = 'bootstrap missing ReadModel TagReadModel import';
}
if (str_contains($bootstrap, 'App\\Infrastructure\\Persistence\\Tag\\TagReadModel')) {
    $errors[] = 'bootstrap still references Persistence TagReadModel';
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "tag-bootstrap-runtime-audit: ok\n";
