<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

/**
 * Canonical JSON responder for host-minimal read endpoints.
 */
final class TagReadResponder
{
    private JsonResponder $json;

    public function __construct()
    {
        $this->json = new JsonResponder();
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function ok(array $body, int $status = 200): array
    {
        return $this->json->respond($status, $body, [], false);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function bad(string $code, int $status = 400, array $extra = []): array
    {
        return $this->json->reject($status, $code, $extra, [], false);
    }
}
