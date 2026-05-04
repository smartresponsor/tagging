<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$forbiddenDirectories = [
    'src/Service/Core/Tag',
    'src/Service/Authz/Tag',
];

foreach ($forbiddenDirectories as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (is_dir($path)) {
        $errors[] = "Forbidden Tag domain wrapper remains: {$relative}";
    }
}

$legacyClasses = [
    'CreateTagCommand',
    'DeleteTagCommand',
    'PatchTagCommand',
    'AssignOperationInterface',
    'UnassignOperationInterface',
    'TransactionRunnerInterface',
    'DoctrineTransactionRunner',
    'QuotaService',
    'TenantGuard',
    'UlidGenerator',
    'CallableTagErrorSink',
    'NullTagErrorSink',
    'IdempotencyMiddleware',
    'OutboxEvent',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/src'));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    foreach ($legacyClasses as $legacyClass) {
        if (preg_match('/\b(class|interface)\s+' . preg_quote($legacyClass, '/') . '\b/', $contents)) {
            $errors[] = "Legacy generic class form remains in {$file->getPathname()}: {$legacyClass}";
        }
    }
}

$composer = file_get_contents($root . '/composer.json');
if (!str_contains($composer, 'App\\\\Tagging\\\\')) {
    $errors[] = 'composer.json must keep the component namespace App\\Tagging\\.';
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo 'Tagging service-depth audit passed.' . PHP_EOL;
