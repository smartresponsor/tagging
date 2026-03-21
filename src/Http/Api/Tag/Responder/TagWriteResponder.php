<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

use App\Application\Write\Tag\Dto\TagError;
use App\Application\Write\Tag\Dto\TagResult;

final class TagWriteResponder
{
    /** @return array<string,string> */
    private function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
        ];
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function respond(TagResult $result): array
    {
        if ($result->ok) {
            if (204 === $result->status) {
                return [204, $this->headers(), ''];
            }

            return [
                $result->status,
                $this->headers(),
                json_encode($result->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}',
            ];
        }

        $status = match ($result->error) {
            TagError::NotFound => 404,
            TagError::Conflict => 409,
            default => 400,
        };

        return [
            $status,
            $this->headers(),
            json_encode(['ok' => false, 'code' => $result->error?->value ?? TagError::ValidationFailed->value], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{"ok":false,"code":"validation_failed"}',
        ];
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function ok(array $body, int $status = 200): array
    {
        return [$status, $this->headers(), json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}'];
    }

    /** @return array{0:int,1:array<string,string>,2:string} */
    public function bad(string $code, int $status = 400, array $extra = []): array
    {
        return $this->ok(['ok' => false, 'code' => $code] + $extra, $status);
    }
}
