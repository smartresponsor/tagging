<?php

declare(strict_types=1);

use App\Kernel;

require dirname(__DIR__) . '/config/bootstrap.php';

return static function (array $context = []): Kernel {
    $env = $context['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
    $debug = (bool) ($context['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? true);

    return new Kernel((string) $env, $debug);
};
