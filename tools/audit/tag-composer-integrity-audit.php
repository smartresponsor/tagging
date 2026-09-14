<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerJsonPath = $root . '/composer.json';
$composerLockPath = $root . '/composer.lock';

if (!is_file($composerJsonPath)) {
    fwrite(STDERR, "composer.json is missing\n");
    exit(1);
}

if (!is_file($composerLockPath)) {
    fwrite(STDERR, "composer.lock is missing\n");
    exit(1);
}

$composerJson = json_decode((string) file_get_contents($composerJsonPath), true, 512, JSON_THROW_ON_ERROR);
$composerLock = json_decode((string) file_get_contents($composerLockPath), true, 512, JSON_THROW_ON_ERROR);

$errors = [];

$autoload = $composerJson['autoload']['psr-4']['App\Tagging\\'] ?? null;
if ($autoload !== 'src/') {
    $errors[] = 'composer.json must map App\Tagging\\ to src/';
}

$requiredPackages = array_keys($composerJson['require'] ?? []);
$requiredDevPackages = array_keys($composerJson['require-dev'] ?? []);
$lockedPackages = array_map(
    static fn(array $package): string => (string) ($package['name'] ?? ''),
    $composerLock['packages'] ?? [],
);
$lockedDevPackages = array_map(
    static fn(array $package): string => (string) ($package['name'] ?? ''),
    $composerLock['packages-dev'] ?? [],
);
$lockedAllPackages = array_values(array_unique(array_merge($lockedPackages, $lockedDevPackages)));

foreach ($requiredPackages as $packageName) {
    if ('php' === $packageName || str_starts_with($packageName, 'ext-')) {
        continue;
    }

    if (!in_array($packageName, $lockedPackages, true)) {
        $errors[] = sprintf('composer.lock is missing required package: %s', $packageName);
    }
}

foreach ($requiredDevPackages as $packageName) {
    if (!in_array($packageName, $lockedAllPackages, true)) {
        $errors[] = sprintf('composer.lock is missing require-dev package: %s', $packageName);
    }
}

foreach (['objecting/object', 'cruding/crud', 'viewing/view', 'interfacing/interface'] as $componentPackage) {
    if (!array_key_exists($componentPackage, $composerJson['require'] ?? [])) {
        $errors[] = sprintf('composer.json is missing required component package: %s', $componentPackage);
    }

    if (!in_array($componentPackage, $lockedPackages, true)) {
        $errors[] = sprintf('composer.lock is missing required component package: %s', $componentPackage);
    }
}

$requiredScripts = [
    'audit:bootstrap-runtime',
    'audit:canonical-stale',
    'audit:canonical-structure',
    'audit:composer-integrity',
    'test',
    'test:unit',
];

foreach ($requiredScripts as $scriptName) {
    if (!array_key_exists($scriptName, $composerJson['scripts'] ?? [])) {
        $errors[] = sprintf('composer.json is missing script: %s', $scriptName);
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, '[composer-integrity] ' . $error . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, "[composer-integrity] OK\n");
