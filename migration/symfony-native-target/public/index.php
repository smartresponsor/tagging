<?php

declare(strict_types=1);

use App\Tagging\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__, 2) . '/config/bootstrap.php';

if (($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? '1') && class_exists(Debug::class)) {
    Debug::enable();
}

$kernel = new Kernel(
    $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev',
    (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? true),
);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
