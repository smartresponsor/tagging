<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

final class TagAssignmentResponder
{
    private JsonResponder $json;

    public function __construct()
    {
        $this->json = new JsonResponder();
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function success(array $body, int $status = 200): array
    {
        return $this->json->respond($status, $body);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function failure(string $code, int $status, array $body = []): array
    {
        return $this->json->reject($status, $code, $body);
    }

    public function statusForCode(?string $code): int
    {
        return match ($code) {
            'tag_not_found' => 404,
            'idempotency_conflict' => 409,
            'assign_failed', 'unassign_failed' => 500,
            'invalid_tenant', 'validation_failed' => 400,
            null, '' => 500,
            default => 500,
        };
    }
}
