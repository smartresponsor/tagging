<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\Dto;

/**
 *
 */

/**
 *
 */
final readonly class TagResult
{
    /** @param array<string,mixed> $payload */
    private function __construct(
        public bool      $ok,
        public int       $status,
        public array     $payload = [],
        public ?TagError $error = null,
    )
    {
    }

    /** @param array<string,mixed> $payload */
    public static function success(int $status, array $payload = []): self
    {
        return new self(true, $status, $payload);
    }

    /**
     * @param \App\Application\Tag\Dto\TagError $error
     * @return self
     */
    public static function failure(TagError $error): self
    {
        return new self(false, 0, [], $error);
    }
}
