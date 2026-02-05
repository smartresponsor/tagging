<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\Dto;

final class TagResult
{
    /** @param array<string,mixed> $payload */
    private function __construct(
        public readonly bool $ok,
        public readonly int $status,
        public readonly array $payload = [],
        public readonly ?TagError $error = null,
    ) {
    }

    /** @param array<string,mixed> $payload */
    public static function success(int $status, array $payload = []): self
    {
        return new self(true, $status, $payload);
    }

    public static function failure(TagError $error): self
    {
        return new self(false, 0, [], $error);
    }
}
