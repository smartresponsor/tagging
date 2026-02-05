<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag\Responder;

use App\Application\Tag\Dto\TagError;
use App\Application\Tag\Dto\TagResult;

final class TagWriteResponder
{
    /** @return array{0:int,1:array<string,string>,2:string} */
    public function respond(TagResult $result): array
    {
        if ($result->ok) {
            if ($result->status === 204) {
                return [204, ['Content-Type' => 'application/json'], ''];
            }

            return [
                $result->status,
                ['Content-Type' => 'application/json'],
                json_encode($result->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}',
            ];
        }

        $status = match ($result->error) {
            TagError::InvalidTenant, TagError::ValidationFailed => 400,
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
