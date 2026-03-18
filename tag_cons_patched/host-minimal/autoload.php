<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__);
$vendorAutoload = $root . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
    return;
}

spl_autoload_register(static function (string $class) use ($root): void {
    $prefixes = [
        'App\\' => $root . '/src/',
        'SR\\SDK\\' => $root . '/sdk/php/',
        'Tests\\' => $root . '/tests/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        $path = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
        return;
    }
});
