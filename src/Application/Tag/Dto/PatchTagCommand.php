<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Application\Tag\Dto;

final class PatchTagCommand
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public readonly string $tenant,
        public readonly string $id,
        public readonly array $payload,
    ) {
    }
}
