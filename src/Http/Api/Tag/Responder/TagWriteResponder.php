<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Api\Tag\Responder;

use App\Application\Write\Tag\Dto\TagError;
use App\Application\Write\Tag\Dto\TagResult;

final class TagWriteResponder
{
    /** @return array{0:int,1:array<string,string>,2:string} */
    public function respond(TagResult $result): array
    {
        if ($result->ok) {
            if (204 === $result->status) {
                return [204, ['Content-Type' => 'application/json'], ''];
            }

            return [
                $result->status,
                ['Content-Type' => 'application/json'],
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
            ['Content-Type' => 'application/json'],
            json_encode(['code' => $result->error?->value ?? TagError::ValidationFailed->value]) ?: '{"code":"validation_failed"}',
        ];
    }
}
