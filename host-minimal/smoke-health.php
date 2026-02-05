<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/** @var array<string, callable():mixed> $container */
$container = require __DIR__.'/bootstrap.php';
/** @var callable(array<string, callable():mixed>):callable(string,string,array<string,mixed>):array{0:int,1:array<string,string>,2:string} $routerFactory */
$routerFactory = require __DIR__.'/route.php';
$dispatch = $routerFactory($container);
$result = $dispatch('GET', '/tag/_status', ['body' => null]);

if (($result[0] ?? 500) === 200) {
    echo "ok\n";
    exit(0);
}

fwrite(STDERR, "health route returned non-200\n");
exit(1);
