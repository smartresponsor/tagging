<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/** @var callable(array<string, callable():mixed>):array<int,array{method:string,pattern:string,handler:callable}> $routeFactory */
$routeFactory = require __DIR__.'/route.php';

$container = [
    'statusController' => static fn (): object => new class {
        /** @return array<string,string> */
        public function status(): array
        {
            return ['status' => 'ok'];
        }
    },
    'idempotencyMiddleware' => static fn (): object => new stdClass(),
    'tagController' => static fn (): object => new stdClass(),
    'assignController' => static fn (): object => new stdClass(),
    'searchController' => static fn (): object => new stdClass(),
    'suggestController' => static fn (): object => new stdClass(),
    'assignmentReadController' => static fn (): object => new stdClass(),
    'synonymController' => static fn (): object => new stdClass(),
    'redirectController' => static fn (): object => new stdClass(),
];
$routes = $routeFactory($container);
$norm = ['body' => null];

foreach ($routes as $route) {
    if ($route['method'] !== 'GET' || $route['pattern'] !== '#^/tag/_status$#') {
        continue;
    }

    $result = $route['handler']($norm, []);
    if (($result[0] ?? 500) === 200) {
        echo "ok\n";
        exit(0);
    }

    fwrite(STDERR, "health route returned non-200\n");
    exit(1);
}

fwrite(STDERR, "health route not found\n");
exit(1);
