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

$autoload = $composerJson['autoload']['psr-4']['App\\'] ?? null;
if ($autoload !== 'src/') {
    $errors[] = 'composer.json must map App\\ to src/';
}

$requiredDevPackages = array_keys($composerJson['require-dev'] ?? []);
$lockedDevPackages = array_map(
    static fn(array $package): string => (string) ($package['name'] ?? ''),
    $composerLock['packages-dev'] ?? []
);

foreach ($requiredDevPackages as $packageName) {
    if (!in_array($packageName, $lockedDevPackages, true)) {
        $errors[] = sprintf('composer.lock is missing require-dev package: %s', $packageName);
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
