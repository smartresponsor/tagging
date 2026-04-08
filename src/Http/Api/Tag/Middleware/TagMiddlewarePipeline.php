<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Middleware;

final readonly class TagMiddlewarePipeline
{
    /**
     * @param list<object> $middlewares
     */
    public function __construct(private array $middlewares)
    {
    }

    /** @param callable(array<string,mixed>):array{0:int,1:array<string,string>,2:string} $destination */
    public function handle(array $request, callable $destination): array
    {
        $next = $destination;

        for ($index = count($this->middlewares) - 1; $index >= 0; --$index) {
            $middleware = $this->middlewares[$index];
            $current = $next;
            $next = static function (array $req) use ($middleware, $current): array {
                return $middleware->handle($req, $current);
            };
        }

        return $next($request);
    }
}
