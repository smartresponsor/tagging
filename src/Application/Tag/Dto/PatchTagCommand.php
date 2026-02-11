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
final readonly class PatchTagCommand
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public string $tenant,
        public string $id,
        public array  $payload,
    )
    {
    }
}
