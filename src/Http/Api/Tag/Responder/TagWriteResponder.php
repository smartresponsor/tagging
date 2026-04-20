<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tagging\Http\Api\Tag\Responder;

use App\Tagging\Application\Write\Tag\Dto\TagError;
use App\Tagging\Application\Write\Tag\Dto\TagResult;

final class TagWriteResponder
{
    private JsonResponder $json;

    public function __construct()
    {
        $this->json = new JsonResponder();
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function respond(TagResult $result): array
    {
        if ($result->ok) {
            if (204 === $result->status) {
                return $this->json->empty();
            }

            return $this->json->respond($result->status, $result->payload);
        }

        $status = match ($result->error) {
            TagError::NotFound => 404,
            TagError::Conflict => 409,
            default => 400,
        };

        return $this->json->reject($status, $result->error?->value ?? TagError::ValidationFailed->value);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function ok(array $body, int $status = 200): array
    {
        return $this->json->respond($status, $body);
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function bad(string $code, int $status = 400, array $extra = []): array
    {
        return $this->json->reject($status, $code, $extra);
    }
}
