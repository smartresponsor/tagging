<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

final readonly class TagMiddlewareResponder
{
    public function __construct(private JsonResponder $json = new JsonResponder())
    {
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function reject(int $status, string $code, array $payload = [], array $headers = []): array
    {
        return $this->json->reject($status, $code, $payload, $headers);
    }
}
